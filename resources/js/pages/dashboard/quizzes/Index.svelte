<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, useForm, router } from '@inertiajs/svelte';

    interface QuizWeek {
        id: number;
        label: string;
        week_number: number;
        month: number;
        year: number;
        theme: string | null;
        questions_count?: number;
    }

    interface Question {
        id: number;
        type: 'multiple_choice' | 'fill_blank' | 'image_guess';
        content: string;
        options: string[] | null;
        correct_answer: string;
        image_path: string | null;
        points: number;
        difficulty: 'easy' | 'medium' | 'hard';
        is_active: boolean;
    }

    let {
        quizWeeks = [],
        selectedWeek = null,
        questions = [],
    }: {
        quizWeeks: QuizWeek[];
        selectedWeek: QuizWeek | null;
        questions: Question[];
    } = $props();

    // --- Week creation modal state ---
    let showWeekModal = $state(false);
    const currentDate = new Date();
    const weekForm = useForm({
        week_number: 1,
        month: currentDate.getMonth() + 1,
        year: currentDate.getFullYear(),
        theme: '',
    });

    // --- Question form state ---
    const questionForm = useForm({
        quiz_week_id: selectedWeek?.id ?? '',
        type: 'multiple_choice' as 'multiple_choice' | 'fill_blank' | 'image_guess',
        content: '',
        options: ['', '', '', ''],
        correct_answer: '',
        image: null as File | null,
        points: 10,
        difficulty: 'easy' as 'easy' | 'medium' | 'hard',
    });

    // Keep quiz_week_id in sync with the selected week
    $effect(() => {
        questionForm.quiz_week_id = selectedWeek?.id ?? '';
    });

    let imagePreview = $state<string | null>(null);

    function selectWeek(weekId: number) {
        router.get('/dashboard/quizzes', { week_id: weekId }, { preserveState: false });
    }

    function submitWeek() {
        weekForm.post('/dashboard/quizzes/weeks', {
            onSuccess: () => {
                showWeekModal = false;
                weekForm.reset();
            },
        });
    }

    function handleImageChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files && input.files[0]) {
            questionForm.image = input.files[0];
            imagePreview = URL.createObjectURL(input.files[0]);
        }
    }

    function submitQuestion() {
        questionForm.post('/dashboard/quizzes/questions', {
            forceFormData: true,
            onSuccess: () => {
                questionForm.reset();
                questionForm.quiz_week_id = selectedWeek?.id ?? '';
                imagePreview = null;
            },
        });
    }

    function deleteQuestion(questionId: number) {
        if (confirm('Bạn có chắc muốn xóa câu hỏi này?')) {
            router.delete(`/dashboard/quizzes/questions/${questionId}`, {
                data: {},
                preserveScroll: true,
            });
        }
    }

    const difficultyLabels = { easy: 'Dễ', medium: 'Trung bình', hard: 'Khó' };
    const difficultyColors = {
        easy: 'bg-green-100 text-green-700',
        medium: 'bg-amber-100 text-amber-700',
        hard: 'bg-red-100 text-red-700',
    };
    const typeIcons = {
        multiple_choice: 'list_alt',
        fill_blank: 'text_fields',
        image_guess: 'image',
    };
    const typeLabels = {
        multiple_choice: 'Trắc nghiệm',
        fill_blank: 'Điền khuyết',
        image_guess: 'Đuổi hình',
    };
</script>

<svelte:head>
    <title>Đố Vui Kinh Thánh - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">

            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-1">
                        <Link href="/dashboard">Quản trị</Link>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-primary font-medium">Đố vui Kinh Thánh</span>
                    </nav>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Quản lý Đố Vui Kinh Thánh</h1>
                </div>
                <button
                    onclick={() => (showWeekModal = true)}
                    class="duolingo-shadow-primary bg-tertiary text-on-tertiary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all"
                >
                    <span class="material-symbols-outlined">add</span>
                    Tạo tuần mới
                </button>
            </div>

            <!-- Week Filter Bar -->
            <section class="glass-card p-5 rounded-xl shadow-sm border border-outline-variant/20 mb-gutter">
                <h3 class="font-title-md text-[16px] text-on-surface mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">calendar_month</span>
                    Chọn tuần
                </h3>
                {#if quizWeeks.length === 0}
                    <p class="text-on-surface-variant text-sm italic">Chưa có tuần nào. Hãy tạo tuần đầu tiên!</p>
                {:else}
                    <div class="flex flex-wrap gap-3">
                        {#each quizWeeks as week (week.id)}
                            <button
                                onclick={() => selectWeek(week.id)}
                                class="px-4 py-2 rounded-full border-2 font-label-bold text-sm transition-all {selectedWeek?.id === week.id
                                    ? 'border-primary bg-primary text-on-primary'
                                    : 'border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary'}"
                            >
                                {week.label}
                                {#if week.questions_count !== undefined}
                                    <span class="ml-1 opacity-70">({week.questions_count})</span>
                                {/if}
                            </button>
                        {/each}
                    </div>
                {/if}
            </section>

            {#if selectedWeek}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">

                    <!-- Left Column: Add Question Form -->
                    <div class="lg:col-span-7 space-y-gutter">
                        <section class="glass-card p-6 rounded-xl shadow-sm border border-outline-variant/20">
                            <h3 class="font-title-md text-[16px] text-on-surface mb-5 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">edit_note</span>
                                Thêm câu hỏi cho <span class="text-primary ml-1">{selectedWeek.label}</span>
                            </h3>

                            <form onsubmit={(e) => { e.preventDefault(); submitQuestion(); }} class="space-y-5">

                                <!-- Question Type Selector -->
                                <div>
                                    <label class="block font-label-bold text-on-surface-variant mb-3">Loại câu hỏi</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        {#each (['multiple_choice', 'fill_blank', 'image_guess'] as const) as type}
                                            <label class="cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="qtype"
                                                    value={type}
                                                    bind:group={questionForm.type}
                                                    class="hidden peer"
                                                />
                                                <div class="p-4 text-center border-2 rounded-xl peer-checked:border-primary peer-checked:bg-primary-container/20 hover:bg-surface-variant transition-all">
                                                    <span class="material-symbols-outlined block mb-1 text-primary">{typeIcons[type]}</span>
                                                    <p class="text-xs font-label-bold">{typeLabels[type]}</p>
                                                </div>
                                            </label>
                                        {/each}
                                    </div>
                                </div>

                                <!-- Question Content -->
                                <div>
                                    <label class="block font-label-bold text-on-surface mb-2">Nội dung câu hỏi</label>
                                    <textarea
                                        bind:value={questionForm.content}
                                        rows="3"
                                        placeholder="Nhập câu hỏi Kinh Thánh tại đây..."
                                        class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 p-4 resize-none outline-none text-on-surface"
                                    ></textarea>
                                    {#if questionForm.errors.content}<span class="text-error text-xs">{questionForm.errors.content}</span>{/if}
                                </div>

                                <!-- Multiple Choice Options -->
                                {#if questionForm.type === 'multiple_choice'}
                                    <div class="space-y-3 border-t border-outline-variant/20 pt-4">
                                        <p class="font-label-bold text-primary">Các tùy chọn đáp án</p>
                                        {#each questionForm.options as _, i}
                                            <div class="flex items-center gap-3">
                                                <input
                                                    type="radio"
                                                    name="correct_radio"
                                                    value={questionForm.options[i]}
                                                    onchange={() => { questionForm.correct_answer = questionForm.options[i]; }}
                                                    class="text-primary focus:ring-primary"
                                                />
                                                <input
                                                    type="text"
                                                    bind:value={questionForm.options[i]}
                                                    placeholder="Đáp án {String.fromCharCode(65 + i)}"
                                                    class="flex-1 bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2 outline-none"
                                                />
                                            </div>
                                        {/each}
                                        <p class="text-xs text-on-surface-variant italic">Chọn nút tròn bên cạnh đáp án đúng</p>
                                    </div>
                                {/if}

                                <!-- Fill Blank Answer -->
                                {#if questionForm.type === 'fill_blank'}
                                    <div class="border-t border-outline-variant/20 pt-4">
                                        <label class="block font-label-bold text-primary mb-2">Đáp án điền vào chỗ trống</label>
                                        <input
                                            type="text"
                                            bind:value={questionForm.correct_answer}
                                            placeholder="Nhập đáp án đúng..."
                                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2 outline-none"
                                        />
                                    </div>
                                {/if}

                                <!-- Image Guess -->
                                {#if questionForm.type === 'image_guess'}
                                    <div class="space-y-3 border-t border-outline-variant/20 pt-4">
                                        <p class="font-label-bold text-primary">Ảnh đuổi hình bắt chữ</p>
                                        <label class="block p-6 bg-surface-container rounded-xl border-2 border-dashed border-outline-variant text-center cursor-pointer hover:border-primary transition-colors">
                                            {#if imagePreview}
                                                <img src={imagePreview} alt="Preview" class="max-h-48 mx-auto rounded-lg object-cover mb-2" />
                                                <p class="text-xs text-primary font-label-bold">Nhấp để thay đổi ảnh</p>
                                            {:else}
                                                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2 block">cloud_upload</span>
                                                <p class="text-on-surface-variant font-label-bold">Tải ảnh lên</p>
                                                <p class="text-xs text-outline italic">Kéo thả hoặc nhấp để chọn tệp</p>
                                            {/if}
                                            <input type="file" accept="image/*" onchange={handleImageChange} class="hidden" />
                                        </label>
                                        <div>
                                            <label class="block font-label-bold text-primary mb-2">Đáp án (chữ)</label>
                                            <input
                                                type="text"
                                                bind:value={questionForm.correct_answer}
                                                placeholder="Đáp án của hình ảnh..."
                                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2 outline-none"
                                            />
                                        </div>
                                    </div>
                                {/if}

                                <!-- Points & Difficulty -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-label-bold text-on-surface mb-2">Điểm số</label>
                                        <input
                                            type="number"
                                            bind:value={questionForm.points}
                                            min="1"
                                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2 outline-none"
                                        />
                                    </div>
                                    <div>
                                        <label class="block font-label-bold text-on-surface mb-2">Độ khó</label>
                                        <select
                                            bind:value={questionForm.difficulty}
                                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2 outline-none"
                                        >
                                            <option value="easy">Dễ</option>
                                            <option value="medium">Trung bình</option>
                                            <option value="hard">Khó</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="flex justify-end gap-3 pt-2">
                                    <button
                                        type="button"
                                        onclick={() => questionForm.reset()}
                                        class="px-6 py-2 rounded-xl border border-outline-variant text-on-surface-variant font-title-md text-[14px] hover:bg-surface-variant transition-colors"
                                    >
                                        Hủy
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={questionForm.processing}
                                        class="duolingo-shadow-primary px-8 py-2 rounded-xl bg-primary text-on-primary font-title-md text-[14px] hover:brightness-110 transition-all active:scale-95 disabled:opacity-50"
                                    >
                                        {questionForm.processing ? 'Đang lưu...' : 'Lưu câu hỏi'}
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>

                    <!-- Right Column: Question List -->
                    <div class="lg:col-span-5">
                        <section class="glass-card rounded-xl shadow-sm border border-outline-variant/20 h-full flex flex-col">
                            <div class="p-5 border-b border-outline-variant/20 flex justify-between items-center">
                                <h3 class="font-title-md text-[16px] text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">list</span>
                                    Danh sách câu hỏi
                                </h3>
                                <span class="bg-primary-container text-on-primary-container px-3 py-1 rounded-full text-xs font-bold">
                                    {questions.length} Câu
                                </span>
                            </div>

                            {#if questions.length === 0}
                                <div class="flex-1 flex flex-col items-center justify-center p-10 text-center">
                                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">quiz</span>
                                    <p class="text-on-surface-variant font-label-bold">Chưa có câu hỏi nào</p>
                                    <p class="text-xs text-outline italic mt-1">Hãy thêm câu hỏi đầu tiên bên trái!</p>
                                </div>
                            {:else}
                                <div class="overflow-y-auto max-h-[700px] divide-y divide-outline-variant/20">
                                    {#each questions as question (question.id)}
                                        <div class="flex items-start gap-3 p-4 hover:bg-surface-container/50 transition-colors group">
                                            <span class="material-symbols-outlined text-primary mt-0.5 shrink-0">{typeIcons[question.type]}</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold line-clamp-2 text-on-surface">{question.content}</p>
                                                <p class="text-xs text-on-surface-variant mt-1">✅ {question.correct_answer}</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="text-xs px-2 py-0.5 rounded-full {difficultyColors[question.difficulty]}">
                                                        {difficultyLabels[question.difficulty]}
                                                    </span>
                                                    <span class="text-xs text-outline">{question.points} điểm</span>
                                                </div>
                                            </div>
                                            <button
                                                onclick={() => deleteQuestion(question.id)}
                                                class="shrink-0 p-1.5 hover:bg-error-container rounded-full text-outline hover:text-error transition-colors opacity-0 group-hover:opacity-100"
                                                title="Xóa câu hỏi"
                                            >
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </section>
                    </div>

                </div>
            {:else if quizWeeks.length === 0}
                <!-- Empty state -->
                <div class="glass-card rounded-xl p-16 text-center border border-outline-variant/20">
                    <span class="material-symbols-outlined text-6xl text-outline-variant mb-4 block">menu_book</span>
                    <h2 class="font-title-md text-[20px] text-on-surface mb-2">Chưa có tuần câu hỏi nào</h2>
                    <p class="text-on-surface-variant mb-6">Bắt đầu bằng cách tạo tuần câu hỏi đầu tiên!</p>
                    <button
                        onclick={() => (showWeekModal = true)}
                        class="duolingo-shadow-primary bg-primary text-on-primary px-8 py-3 rounded-xl font-title-md text-[16px] hover:brightness-110 active:scale-95 transition-all"
                    >
                        <span class="material-symbols-outlined align-middle mr-1">add</span>
                        Tạo tuần đầu tiên
                    </button>
                </div>
            {/if}

        </div>
    </div>

    <!-- Create Week Modal -->
    {#if showWeekModal}
        <!-- Backdrop -->
        <button
            class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
            onclick={() => (showWeekModal = false)}
            aria-label="Đóng"
        ></button>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="glass-card rounded-2xl p-8 w-full max-w-md shadow-xl border border-outline-variant/20">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-title-md text-[20px] text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">calendar_add_on</span>
                        Tạo tuần câu hỏi mới
                    </h2>
                    <button onclick={() => (showWeekModal = false)} class="p-2 hover:bg-surface-variant rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form onsubmit={(e) => { e.preventDefault(); submitWeek(); }} class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-label-bold text-on-surface-variant mb-2 text-xs">Tuần thứ</label>
                            <select
                                bind:value={weekForm.week_number}
                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-3 py-2 outline-none"
                            >
                                {#each [1,2,3,4,5] as w}
                                    <option value={w}>Tuần {w}</option>
                                {/each}
                            </select>
                        </div>
                        <div>
                            <label class="block font-label-bold text-on-surface-variant mb-2 text-xs">Tháng</label>
                            <select
                                bind:value={weekForm.month}
                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-3 py-2 outline-none"
                            >
                                {#each Array.from({length: 12}, (_, i) => i + 1) as m}
                                    <option value={m}>Tháng {m}</option>
                                {/each}
                            </select>
                        </div>
                        <div>
                            <label class="block font-label-bold text-on-surface-variant mb-2 text-xs">Năm</label>
                            <input
                                type="number"
                                bind:value={weekForm.year}
                                min="2020"
                                max="2100"
                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-3 py-2 outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-label-bold text-on-surface-variant mb-2 text-xs">Chủ đề tuần (không bắt buộc)</label>
                        <input
                            type="text"
                            bind:value={weekForm.theme}
                            placeholder="VD: Tuần lễ Chúa Thánh Thần..."
                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-3 outline-none"
                        />
                    </div>

                    {#if weekForm.errors.week_number || weekForm.errors.month || weekForm.errors.year}
                        <p class="text-error text-sm">{weekForm.errors.week_number ?? weekForm.errors.month ?? weekForm.errors.year}</p>
                    {/if}

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            onclick={() => (showWeekModal = false)}
                            class="px-6 py-2 rounded-xl border border-outline-variant text-on-surface-variant font-label-bold hover:bg-surface-variant transition-colors"
                        >
                            Hủy
                        </button>
                        <button
                            type="submit"
                            disabled={weekForm.processing}
                            class="duolingo-shadow-primary px-8 py-2 rounded-xl bg-primary text-on-primary font-label-bold hover:brightness-110 active:scale-95 transition-all disabled:opacity-50"
                        >
                            {weekForm.processing ? 'Đang tạo...' : 'Tạo tuần'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</DashboardLayout>
