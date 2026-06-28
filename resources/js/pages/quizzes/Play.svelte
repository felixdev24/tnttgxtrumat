<script lang="ts">

    import { page } from '@inertiajs/svelte';
    import { Link } from '@inertiajs/svelte';
    import { onDestroy } from 'svelte';

    // Props from the controller
    let { quizWeek, allWeeks, leaderboard, userTotalScore } = $props();

    // Authentication state
    let user = $derived(page.props.auth?.user);

    // Quiz state
    let currentQuestionIndex = $state(0);
    let questions = $derived(quizWeek?.questions || []);
    let currentQuestion = $derived(questions[currentQuestionIndex]);
    let audio: HTMLAudioElement | null = null;

    $effect(() => {
        // Initialize audio instance
        if (typeof window !== 'undefined') {
            audio = new Audio('/assets/tieng_chuong_het_gio_het_thoi_gian_trong_powerpoint-www_tiengdong_com.mp3');
        }
    });
    
    // Popup and Feedback state
    let showLoginPopup = $state(false);
    let isSubmitting = $state(false);
    let answerFeedback = $state<{status: 'correct' | 'incorrect' | 'error', message: string, points?: number} | null>(null);
    let selectedAnswer = $state<string | null>(null);
    let wrongAnswers = $state<string[]>([]);
    let shakeOption = $state<string | null>(null);

    // Derived values
    let progressPercentage = $derived(questions.length > 0 ? ((currentQuestionIndex + 1) / questions.length) * 100 : 0);
    let userScore = $state(user ? userTotalScore : 0); // Real starting score

    // Timer and Huynh Truong controls
    let timeLeft = $state(45);
    let timerInterval: any = null;
    let isTimerRunning = $state(false);
    let showAnswer = $state(false);

    function startTimer() {
        if (timerInterval) clearInterval(timerInterval);
        isTimerRunning = true;
        timerInterval = setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
            } else {
                clearInterval(timerInterval);
                isTimerRunning = false;
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(e => console.error("Could not play audio", e));
                }
            }
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) clearInterval(timerInterval);
        isTimerRunning = false;
    }

    $effect(() => {
        // Track currentQuestionIndex to trigger effect on question change
        const idx = currentQuestionIndex;
        timeLeft = 45;
        showAnswer = false;
        stopTimer();
        
        if (user?.role !== 'huynh_truong' && questions.length > 0 && !answerFeedback) {
            startTimer();
        }
    });

    onDestroy(() => {
        if (timerInterval) clearInterval(timerInterval);
    });

    function requireAuth(action: () => void) {
        if (!user) {
            showLoginPopup = true;
        } else {
            action();
        }
    }

    function triggerWrongExplosion() {
        if (typeof window !== 'undefined' && (window as any).confetti) {
            // Dark red/gray burst for wrong answer
            (window as any).confetti({
                particleCount: 40,
                spread: 50,
                origin: { y: 0.7 },
                colors: ['#dc2626', '#991b1b', '#7f1d1d', '#451a03'],
                gravity: 1.5,
                scalar: 0.8,
                ticks: 80
            });
        }
    }

    async function handleOptionClick(option: string) {
        if (isSubmitting || answerFeedback) return;
        if (wrongAnswers.includes(option)) return; // Already tried this wrong answer

        requireAuth(async () => {
            selectedAnswer = option;
            isSubmitting = true;
            try {
                // Fetch the CSRF token from the meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const response = await fetch('/quizzes/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        question_id: currentQuestion.id,
                        answer: option
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.is_correct) {
                        // Correct answer - lock the question
                        answerFeedback = {
                            status: 'correct',
                            message: `Chính xác! Bạn được cộng ${data.points_awarded} điểm.`,
                            points: data.points_awarded
                        };
                        userScore += data.points_awarded;
                        if (typeof window !== 'undefined' && (window as any).confetti) {
                            (window as any).confetti({
                                particleCount: 100,
                                spread: 70,
                                origin: { y: 0.6 }
                            });
                        }
                    } else {
                        // Wrong answer - shake, explode, mark as wrong, allow retry
                        wrongAnswers = [...wrongAnswers, option];
                        shakeOption = option;
                        triggerWrongExplosion();
                        setTimeout(() => { shakeOption = null; }, 600);
                        selectedAnswer = null;
                    }
                } else if (response.status === 422 && data.message) {
                    if (data.is_correct) {
                        // Already answered correctly before - show success
                        answerFeedback = {
                            status: 'correct',
                            message: 'Bạn đã trả lời đúng câu hỏi này rồi!',
                            points: 0
                        };
                    } else {
                        // Already answered wrong - mark and allow retry
                        wrongAnswers = [...wrongAnswers, option];
                        shakeOption = option;
                        triggerWrongExplosion();
                        setTimeout(() => { shakeOption = null; }, 600);
                        selectedAnswer = null;
                    }
                } else {
                    throw new Error('Server error');
                }
            } catch (error: any) {
                answerFeedback = {
                    status: 'error',
                    message: 'Có lỗi xảy ra, vui lòng thử lại sau.'
                };
            } finally {
                isSubmitting = false;
            }
        });
    }

    async function checkAnswer(answer: string) {
        handleOptionClick(answer);
    }

    function nextQuestion() {
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            answerFeedback = null;
            selectedAnswer = null;
            wrongAnswers = [];
            shakeOption = null;
        }
    }

    function skipQuestion() {
        nextQuestion();
    }

    const optionLetters = ['A', 'B', 'C', 'D'];
    const optionColors = [
        'bg-primary-container text-on-primary-container duolingo-button-blue',
        'bg-secondary-container text-on-secondary-container duolingo-button-yellow',
        'bg-tertiary-container text-on-tertiary-container duolingo-button-red',
        'bg-outline-variant text-on-surface-variant duolingo-button-blue'
    ];
    const letterColors = [
        'text-primary',
        'text-secondary',
        'text-tertiary',
        'text-on-surface-variant'
    ];
</script>

<svelte:head>
    <title>Đố Vui Kinh Thánh - Thiếu Nhi Thánh Thể</title>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
</svelte:head>

<main class="pt-[100px] pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto min-h-screen relative font-body-md">
        
        {#if !quizWeek}
            <div class="glass-card rounded-3xl p-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4 block">menu_book</span>
                <h2 class="font-headline-lg text-headline-lg-mobile text-primary mb-2">Chưa có tuần đố vui nào!</h2>
                <p class="text-on-surface-variant text-lg">Hiện tại chưa có câu hỏi đố vui nào được đăng tải. Vui lòng quay lại sau.</p>
            </div>
        {:else}
            <!-- Gamification Stats Bar -->
            <section class="flex flex-wrap justify-between items-center gap-stack-md mb-stack-lg animate-fade-in">
                <div class="flex gap-stack-md">
                    <div class="glass-card px-stack-md py-2 rounded-full flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">monetization_on</span>
                        <span class="font-label-bold text-primary">{user ? userScore.toLocaleString() : '0'}</span>
                    </div>
                    <div class="glass-card px-stack-md py-2 rounded-full flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">local_fire_department</span>
                        <span class="font-label-bold text-primary">{user ? '5 Ngày' : '0 Ngày'}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="w-10 h-10 rounded-full bg-tertiary-fixed flex items-center justify-center border-2 border-white shadow-sm" title="Huy hiệu Cầu Nguyện">
                        <span class="material-symbols-outlined text-tertiary text-lg" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center border-2 border-white shadow-sm" title="Huy hiệu Siêng Năng">
                        <span class="material-symbols-outlined text-primary text-lg" style="font-variation-settings: 'FILL' 1;">verified</span>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center border-2 border-white shadow-sm" title="Huy hiệu Thánh Thể">
                        <span class="material-symbols-outlined text-secondary text-lg" style="font-variation-settings: 'FILL' 1;">star</span>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
                
                <!-- Left Side Mascot (Desktop) -->
                <div class="hidden lg:block lg:col-span-3 sticky top-[120px] text-center">
                    <img alt="Mascot TNTT" class="w-48 mx-auto mb-stack-md drop-shadow-xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBEscOiENRgV9Den5rjZNSkrrz92j8HdPncWNpn82WfncZg7-UoxE8HplcOqtO5RwuOxXqaL_o5q_6oDgWmVgsKYZ7MwIeZunDiICmExfY-zDaWmWiFjHOzitUNgRB4J9CmcgS9qTdF7TecwNfxffbTmWQqpI1zeSwAHlMmOIP7jS_gHu4ln2zw9aPQnHcxzBI9381R1BDbC8lD793krYlXihmx4IoM4ZCNQYIrwaozA4vnSuDHVE_b2WXAj_1Z6RRJQr52F6LfS4I"/>
                    <div class="glass-card p-stack-md rounded-xl shadow-sm border border-white/50">
                        <p class="font-label-bold text-primary mb-1">Huynh Trưởng nói:</p>
                        <p class="font-body-md text-on-surface-variant italic">"Cố lên em nhé! Chúa Giê-su đang mỉm cười với em đó!"</p>
                    </div>
                </div>

                <!-- Central Quiz Card -->
                <div class="lg:col-span-6 space-y-gutter">
                    {#if questions.length === 0}
                        <div class="glass-card p-10 text-center rounded-2xl shadow-lg border border-white/50">
                            <h3 class="font-headline-lg text-primary mb-2">Tuần này chưa có câu hỏi</h3>
                            <p class="text-on-surface-variant">Huynh trưởng đang soạn câu hỏi, bạn chờ chút nhé!</p>
                        </div>
                    {:else}
                        <div class="glass-card p-stack-lg rounded-3xl shadow-xl border-2 border-primary/20 relative overflow-hidden bg-white/40">
                            <!-- Progress Bar -->
                            <div class="absolute top-0 left-0 w-full h-2 bg-surface-container-highest">
                                <div class="h-full bg-primary transition-all duration-500 ease-out rounded-r-full" style="width: {progressPercentage}%"></div>
                            </div>
                            
                            <div class="mt-4 flex justify-between items-center text-primary font-label-bold mb-stack-lg">
                                <span class="bg-primary/10 px-3 py-1 rounded-full border border-primary/20">Câu hỏi {currentQuestionIndex + 1}/{questions.length}</span>
                                <span class="flex items-center gap-1 bg-surface-container px-3 py-1 rounded-full {timeLeft <= 10 ? 'text-error animate-pulse' : ''}">
                                    <span class="material-symbols-outlined text-sm">timer</span>
                                    00:{timeLeft.toString().padStart(2, '0')}
                                </span>
                            </div>
                            
                            <!-- Question Content -->
                            <div class="min-h-[160px] flex items-center justify-center">
                                <h2 class="font-headline-lg text-headline-lg-mobile text-on-surface text-center py-4 px-2 leading-tight">
                                    {currentQuestion.content}
                                </h2>
                            </div>
                            
                            {#if currentQuestion.image_path}
                                <img src={currentQuestion.image_path} alt="Câu hỏi hình ảnh" class="w-full max-h-64 object-contain rounded-xl my-4 shadow-sm" />
                            {/if}
                        </div>

                        <!-- Answers Form -->
                        {#if currentQuestion.type === 'multiple_choice'}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md relative">
                                {#each currentQuestion.options as option, i}
                                    <button 
                                        onclick={() => handleOptionClick(option)}
                                        disabled={isSubmitting || !!answerFeedback || wrongAnswers.includes(option)}
                                        class="
                                            p-5 rounded-2xl flex items-center gap-stack-md transition-all group active:scale-[0.98] disabled:active:scale-100
                                            {(showAnswer || !!answerFeedback) && option == currentQuestion.correct_answer ? 'bg-green-500 text-white ring-4 ring-green-600 scale-[1.02] !opacity-100 shadow-lg font-bold' : wrongAnswers.includes(option) ? 'opacity-40 line-through bg-red-100 border-2 border-red-300 cursor-not-allowed' : optionColors[i % 4] + ' hover:opacity-90'}
                                            {shakeOption === option ? 'animate-wrong-shake' : ''}
                                        "
                                    >
                                        <span class="w-12 h-12 rounded-xl flex items-center justify-center font-headline-lg text-2xl shadow-sm {wrongAnswers.includes(option) ? 'bg-red-200 text-red-600' : 'bg-white/60 ' + letterColors[i % 4]}">
                                            {#if wrongAnswers.includes(option)}
                                                <span class="material-symbols-outlined text-red-500 text-2xl">close</span>
                                            {:else}
                                                {optionLetters[i]}
                                            {/if}
                                        </span>
                                        <span class="font-title-md text-left flex-1 {wrongAnswers.includes(option) ? 'text-red-400' : ''}">{option}</span>
                                        {#if isSubmitting && selectedAnswer === option}
                                            <span class="material-symbols-outlined animate-spin">progress_activity</span>
                                        {/if}
                                    </button>
                                {/each}
                            </div>
                        {:else}
                            <!-- Fill in the blank or image guess -->
                            <div class="flex flex-col gap-3">
                                <input 
                                    type="text" 
                                    bind:value={selectedAnswer}
                                    disabled={isSubmitting || !!answerFeedback}
                                    placeholder="Nhập câu trả lời của bạn..." 
                                    class="w-full bg-white border-2 border-outline-variant/30 focus:border-primary focus:ring-4 focus:ring-primary/20 rounded-2xl px-6 py-4 font-title-md text-lg outline-none shadow-sm transition-all disabled:opacity-70"
                                >
                                {#if showAnswer || !!answerFeedback}
                                    <div class="p-4 rounded-xl bg-green-100 border border-green-500 text-green-900 font-label-bold">
                                        Đáp án đúng: {currentQuestion.correct_answer}
                                    </div>
                                {/if}
                            </div>
                        {/if}

                        <!-- Feedback Message -->
                        {#if answerFeedback}
                            <div class="mt-stack-md p-4 rounded-xl border-2 flex items-center justify-between gap-4 animate-scale-in {answerFeedback.status === 'correct' ? 'bg-primary-container/30 border-primary text-primary' : answerFeedback.status === 'incorrect' ? 'bg-error-container/30 border-error text-error' : 'bg-surface-variant text-on-surface-variant'}">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-3xl">
                                        {answerFeedback.status === 'correct' ? 'check_circle' : answerFeedback.status === 'incorrect' ? 'cancel' : 'info'}
                                    </span>
                                    <span class="font-label-bold text-lg">{answerFeedback.message}</span>
                                </div>
                                <button onclick={nextQuestion} class="bg-primary text-on-primary px-6 py-3 rounded-full font-label-bold shadow-md hover:shadow-lg transition-all active:scale-95 flex items-center gap-2 whitespace-nowrap">
                                    Tiếp theo <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </div>
                        {/if}

                        <!-- Navigation -->
                        {#if !answerFeedback}
                            <!-- Huynh Truong Controls -->
                            {#if user?.role === 'huynh_truong'}
                                <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-outline-variant/30">
                                    <span class="text-sm font-label-bold text-on-surface-variant w-full md:w-auto mr-2">Huynh Trưởng:</span>
                                    {#if !isTimerRunning && timeLeft === 45}
                                        <button onclick={startTimer} class="flex items-center gap-1 bg-tertiary text-on-tertiary px-4 py-2 rounded-lg font-bold shadow-sm hover:brightness-110 active:scale-95 transition-all text-sm">
                                            <span class="material-symbols-outlined text-[18px]">play_arrow</span> Bắt đầu giờ
                                        </button>
                                    {:else if isTimerRunning}
                                        <button onclick={stopTimer} class="flex items-center gap-1 bg-error text-on-error px-4 py-2 rounded-lg font-bold shadow-sm hover:brightness-110 active:scale-95 transition-all text-sm">
                                            <span class="material-symbols-outlined text-[18px]">pause</span> Dừng giờ
                                        </button>
                                    {:else}
                                        <button onclick={startTimer} class="flex items-center gap-1 bg-secondary text-on-secondary px-4 py-2 rounded-lg font-bold shadow-sm hover:brightness-110 active:scale-95 transition-all text-sm">
                                            <span class="material-symbols-outlined text-[18px]">resume</span> Tiếp tục
                                        </button>
                                    {/if}
                                    
                                    <button onclick={() => showAnswer = !showAnswer} class="flex items-center gap-1 bg-surface-variant text-on-surface-variant px-4 py-2 rounded-lg font-bold border border-outline-variant/50 shadow-sm hover:bg-surface-container-high active:scale-95 transition-all text-sm">
                                        <span class="material-symbols-outlined text-[18px]">{showAnswer ? 'visibility_off' : 'visibility'}</span>
                                        {showAnswer ? 'Ẩn đáp án' : 'Hiện đáp án'}
                                    </button>
                                </div>
                            {/if}

                            <div class="flex justify-between items-center pt-stack-md mt-2">
                                <button onclick={skipQuestion} disabled={isSubmitting} class="text-on-surface-variant font-label-bold flex items-center gap-1 hover:text-primary transition-colors px-4 py-2 rounded-full hover:bg-primary/5 disabled:opacity-50">
                                    <span class="material-symbols-outlined">arrow_back</span> Bỏ qua
                                </button>
                                {#if currentQuestion.type !== 'multiple_choice'}
                                    <button onclick={() => selectedAnswer && checkAnswer(selectedAnswer)} disabled={isSubmitting || !selectedAnswer} class="bg-primary text-on-primary px-8 py-4 rounded-full font-label-bold shadow-lg hover:shadow-xl hover:-translate-y-1 active:scale-95 transition-all flex items-center gap-2 disabled:opacity-50 disabled:hover:translate-y-0 disabled:active:scale-100">
                                        {#if isSubmitting}
                                            <span class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                                        {:else}
                                            Kiểm tra đáp án <span class="material-symbols-outlined text-sm">send</span>
                                        {/if}
                                    </button>
                                {/if}
                            </div>
                        {/if}
                    {/if}
                </div>

                <!-- Right Sidebar Achievements -->
                <aside class="lg:col-span-3 space-y-gutter">
                    <!-- Week selector (if you want them to be able to navigate past weeks) -->
                    <div class="glass-card p-stack-md rounded-2xl shadow-sm border border-white/50">
                        <h3 class="font-label-bold text-primary mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined">calendar_month</span> Chủ đề tuần
                        </h3>
                        <div class="bg-surface-container-low p-3 rounded-xl border border-outline-variant/20">
                            <p class="font-title-md text-sm text-on-surface">{quizWeek.label}</p>
                            {#if quizWeek.theme}
                                <p class="text-xs text-on-surface-variant mt-1">{quizWeek.theme}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="glass-card p-stack-md rounded-2xl shadow-sm border border-white/50">
                        <h3 class="font-label-bold text-primary mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined">emoji_events</span> Bảng Xếp Hạng
                        </h3>
                        <div class="space-y-2">
                            {#each leaderboard as userRank}
                                <div class="flex justify-between items-center p-2.5 rounded-xl transition-colors {userRank.is_me ? 'bg-primary-container/30 border border-primary/30' : 'bg-surface-container-lowest hover:bg-surface-container-low'}">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 text-center font-bold {userRank.rank <= 3 ? 'text-tertiary' : 'text-on-surface-variant'}">
                                            {userRank.rank}
                                        </span>
                                        <span class="font-body-md {userRank.is_me ? 'font-bold text-primary' : ''}">
                                            {userRank.name}
                                        </span>
                                    </div>
                                    <span class="font-label-bold text-primary bg-primary/5 px-2 py-1 rounded-md">{userRank.score}</span>
                                </div>
                            {/each}
                        </div>
                        <Link href="/quizzes/leaderboard" class="w-full mt-4 text-sm text-primary font-label-bold hover:bg-primary/5 py-2 rounded-xl transition-colors text-center block">Xem tất cả</Link>
                    </div>

                    <div class="glass-card p-stack-md rounded-2xl bg-gradient-to-br from-tertiary-fixed to-white border-none shadow-md">
                        <div class="flex items-start gap-4">
                            <div class="bg-white p-3 rounded-2xl shadow-sm">
                                <span class="material-symbols-outlined text-tertiary text-2xl" style="font-variation-settings: 'FILL' 1;">ads_click</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-title-md text-on-tertiary-fixed text-[16px]">Nhiệm vụ hằng ngày</p>
                                <p class="text-xs text-on-tertiary-fixed-variant mb-3 font-medium">Trả lời 5 câu hỏi đúng liên tiếp</p>
                                <div class="w-full h-2 bg-white/60 rounded-full overflow-hidden">
                                    <div class="w-3/5 h-full bg-tertiary rounded-full shadow-[inset_0_1px_3px_rgba(0,0,0,0.2)]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
            
            <!-- Mobile Mascot -->
            <div class="lg:hidden fixed bottom-24 right-4 z-40">
                <div class="relative">
                    <div class="absolute -top-12 -left-24 bg-white p-2.5 rounded-xl shadow-lg text-xs font-bold text-primary animate-bounce border-2 border-primary-container">
                        Cố lên bạn ơi! ✨
                    </div>
                    <img alt="Small Mascot" class="w-16 h-16 rounded-full border-4 border-white shadow-xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAoTk97GTKrFthPNiHUgFDL3v2MURsfEm_QB75h2-E49vLJEG8tZ3S2h5r4wajqSNmi7sBfUyqEUcYOWnJkIOaDtwOdrcJlUSRedw3YB180w-AAnaBHp8qJBv0qCBYzNDt-VQbzb5JCCn75_gMoMIgEXWJKEPLyz4J41Iw1FKZRlg5O8tLK76WTsN5GwcgUlLLlJ1XDK7oYNRlxW8s_637zbePlFDwdsqz4CgFtraS3bj9z1O8PT6yvAwtAyG5oTrUOJceqKs4le7o"/>
                </div>
            </div>
        {/if}
    </main>

    <!-- Login Required Popup -->
    {#if showLoginPopup}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4 animate-fade-in font-body-md">
            <div class="bg-white dark:bg-surface-dim rounded-3xl p-8 max-w-sm w-full text-center shadow-2xl scale-in transform border border-outline-variant/20 relative overflow-hidden">
                <!-- Decorative background shapes -->
                <div class="absolute top-0 left-0 w-full h-32 bg-primary-fixed/30 rounded-b-[100%] scale-150 -translate-y-10 z-0"></div>
                
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-primary-container rounded-full flex items-center justify-center mx-auto mb-6 shadow-md border-4 border-white">
                        <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">vpn_key</span>
                    </div>
                    
                    <h3 class="font-headline-lg text-2xl text-on-surface mb-3">Chưa Đăng Nhập</h3>
                    
                    <p class="text-on-surface-variant text-base mb-8 leading-relaxed">
                        Bạn cần đăng nhập bằng tài khoản Giáo xứ để lưu điểm số, nhận huy hiệu và tham gia Bảng Xếp Hạng Đố Vui nhé!
                    </p>
                    
                    <div class="flex flex-col gap-3">
                        <Link 
                            href="/login" 
                            class="w-full bg-primary text-on-primary py-3.5 rounded-2xl font-title-md text-[16px] shadow-lg hover:shadow-xl hover:-translate-y-0.5 active:scale-95 transition-all"
                        >
                            Đến Trang Đăng Nhập
                        </Link>
                        <button 
                            onclick={() => showLoginPopup = false} 
                            class="w-full py-3 text-primary font-label-bold hover:bg-primary/5 rounded-2xl transition-colors"
                        >
                            Để sau
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {/if}

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .duolingo-button-red { box-shadow: 0 4px 0 #930005; }
    .duolingo-button-red:active { box-shadow: 0 0 0 #930005; transform: translateY(4px); }
    .duolingo-button-blue { box-shadow: 0 4px 0 #294964; }
    .duolingo-button-blue:active { box-shadow: 0 0 0 #294964; transform: translateY(4px); }
    .duolingo-button-yellow { box-shadow: 0 4px 0 #636037; }
    .duolingo-button-yellow:active { box-shadow: 0 0 0 #636037; transform: translateY(4px); }

    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
    .scale-in {
        animation: scaleIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    .animate-wrong-shake {
        animation: wrongShake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes wrongShake {
        0%, 100% { transform: translateX(0); }
        10%, 50%, 90% { transform: translateX(-6px); }
        30%, 70% { transform: translateX(6px); }
    }
</style>
