<script lang="ts">
    import { onMount } from 'svelte';

    let visible = $state(true);
    let fadeOut = $state(false);

    onMount(() => {
        const MIN_DISPLAY_MS = 800;
        const startTime = performance.now();

        const hide = () => {
            const elapsed = performance.now() - startTime;
            const delay = Math.max(0, MIN_DISPLAY_MS - elapsed);

            setTimeout(() => {
                fadeOut = true;
                // Sync DOM removal with next paint after fade transition
                setTimeout(() => {
                    requestAnimationFrame(() => {
                        visible = false;
                    });
                }, 500);
            }, delay);
        };

        if (document.readyState === 'complete') {
            hide();
        } else {
            window.addEventListener('load', hide, { once: true });
            return () => window.removeEventListener('load', hide);
        }
    });
</script>

{#if visible}
    <div
        class="splash-overlay"
        class:splash-fade-out={fadeOut}
        aria-hidden="true"
    >
        <!-- Background glow blobs -->
        <div class="splash-glow-bg">
            <div class="splash-blob splash-blob-red"></div>
            <div class="splash-blob splash-blob-blue"></div>
        </div>

        <!-- Central glow pulse -->
        <div class="splash-glow-center"></div>

        <!-- Scarf carousel -->
        <div class="splash-scarf-stage">
            <img
                alt="Khăn Hồng"
                class="splash-scarf splash-scarf-1"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDseYdJPesZHXSeOfcP75PzdS-kdgaN8ooajuLeQdsBPvCTz_dqZ6ttyk12EpqjcwKv4w5j-JQDxnZmXTQ-7C8ja6DCfSjJYdLDWiIiGBApZym1cPZhKfuUPSf1z_QTOjBLBePzNcGigT32J9g1Ugx9SP3uWAZnNGY9u2QFdktGvhS7pA6Z3wGEBXOoZjX37UeX_xNUXMZoKhJckK3cMpP0dKvynSKdNfHc4xXA-LOPRBpIaPiw32UW4Wgb0q8yiEx2mUp-SWcLZAo"
            />
            <img
                alt="Khăn Vàng"
                class="splash-scarf splash-scarf-2"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuB0TooT0Jqk8jU_kNt9DbPZ3DBQ0pXTfnAb4GBBDop6TsPE36jbutm-OR2ZiRyg9bKv8M7R3xiYkdzd0u3DT15aZi-VobvxJ1tvhQAp45L6o5tAERYMUXImuAPEgly34H8DJ-KMpJXPFEuB-zZudKdvdIP1OtF_9jbEdOhnrQWLzBcnaAKTywTYHv0aMP_8BcDxeEc9c8NRaczqErk2R1ExLeTujmxk0sH4rNrT-1w2CqZiQRvweGJxV-JgYuDjE-1dSfTCYuOfq1c"
            />
            <img
                alt="Khăn Xanh Lá"
                class="splash-scarf splash-scarf-3"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBCRBG0XaYQjKhFeQp7OA7TqGzuJ7Sf1gSp9sVjNZ6ISZgp9_dFKS6-HDHSlDiHe6hB2RPxcovZa2QjBff9QXZFQNve02nZefrwu5FwZGNl_WpMm7N08fxBD45R30tHci3jK-ko3rs2PCEKndTnTQO2qGvtQZn2rGyMhYj70KXZ7NOIjI-1kLS0KeSv5fVyA0jljDD4nqQTDZ-PF5h2kmBvdxQxA_Mgx1Ef5xANty7rc86Nits9KjKpbrsQGuItrXBfjQo_SZbws8Q"
            />
            <img
                alt="Khăn Nâu"
                class="splash-scarf splash-scarf-4"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDt_ASuh7tz3cVPKJlUFlgX3EMmIjpb472nDKKIT9O05-VpU7d7oScsKJOP-59z2Kw7g5xeDJKV_p6Ycs0a9VHNCJvtcEwELbXW8NogSsIMTZPyUA9YGBzVQ_C6QgZALFADLTBUtUCKfsw_21MtUBqSdocv_B6114qwHiQz98UFrA9o6TSKbxsviFGAbl-9AOM-Hel4fmugCsHa-zMsMm08YxCezcHoBIqZE-nP7GCVCgI8M4oFA8t8mmq6iwYf6yc_-IH4jMnbURU"
            />
            <img
                alt="Khăn Xanh Biển"
                class="splash-scarf splash-scarf-5"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_ztjojwtl0u7XjDsacwuaj34d1Fd-Fqk1Cma0WwrXwmyq9CsZQHnxbU3RhBD0KCc_aa6EUMlIeaDfKLQv_itqTVGepI2piht0mrMbljd2Plv6Uh4ijzOYOTY0_2QtTQqFypFTRT4rHN2P-zPBrNGkgpOT1_q4iVZQ7ngdr88_IbbGNv3O-k3_Ak49F8RNJvnvlyS568WDX7vbun03vrj128sxm3FTL7YvlmiMMd3txjqVyEsPzmE4ayfWEd6bmDM2HTnIF-biKck"
            />
            <img
                alt="Khăn Đỏ"
                class="splash-scarf splash-scarf-6"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuD6n-QujzD6kquxVHo2pFa9G3x8uuPTNcDQ7aKWCM-IXthnW73f1U3JLJGRdPUr4Z4h_OWRmYpX0Mbg9ow59jB3XdKdK27l4tIt6ltPXyjiDfd3fm_UItmaY5tOtmHw3-wL_BVl195MWRyyS0tRFAvD0i_RW0KIAC-1zyBH6CXUsdhtZhZNq0xWJoP35xUG0DwT1lf_vpH921cz9-XyYEsMsSqYhhyOUeCefQIBAH4asEQNg6k-ffEVtANBeMYor24n2dMe6qNEzpM"
            />
        </div>

        <!-- Loading text -->
        <div class="splash-text">ĐANG TẢI...</div>
    </div>
{/if}

<style>
    .splash-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #131313;
        transition: opacity 0.5s ease-out;
        will-change: opacity;
    }

    .splash-fade-out {
        opacity: 0;
        pointer-events: none;
    }

    /* Background glow blobs */
    .splash-glow-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        opacity: 0.3;
        pointer-events: none;
    }

    .splash-blob {
        position: absolute;
        width: 24rem;
        height: 24rem;
        border-radius: 50%;
        filter: blur(120px);
    }

    .splash-blob-red {
        top: 25%;
        left: 25%;
        background: rgba(255, 84, 81, 0.1);
    }

    .splash-blob-blue {
        bottom: 25%;
        right: 25%;
        background: rgba(2, 103, 184, 0.05);
    }

    /* Central radial glow */
    .splash-glow-center {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(
            circle,
            rgba(255, 84, 81, 0.15) 0%,
            rgba(255, 84, 81, 0) 70%
        );
        border-radius: 50%;
        pointer-events: none;
        animation: splashGlowPulse 4s ease-in-out infinite;
        z-index: 1;
    }

    @keyframes splashGlowPulse {
        0%,
        100% {
            transform: scale(1);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }
    }

    /* Scarf carousel */
    .splash-scarf-stage {
        position: relative;
        width: 320px;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .splash-scarf {
        position: absolute;
        width: 300px;
        opacity: 0;
        transform: translateX(-150%) scale(0.8);
        filter: drop-shadow(0 0 20px rgba(255, 179, 173, 0.2));
        animation: splashGlideInOut 12s linear infinite;
    }

    @keyframes splashGlideInOut {
        0% {
            transform: translateX(-150%) scale(0.8);
            opacity: 0;
        }
        2% {
            opacity: 1;
        }
        14% {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
        18% {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
        30% {
            transform: translateX(150%) scale(0.8);
            opacity: 0;
        }
        100% {
            transform: translateX(150%) scale(0.8);
            opacity: 0;
        }
    }

    .splash-scarf-1 {
        animation-delay: 0s;
    }
    .splash-scarf-2 {
        animation-delay: 2s;
    }
    .splash-scarf-3 {
        animation-delay: 4s;
    }
    .splash-scarf-4 {
        animation-delay: 6s;
    }
    .splash-scarf-5 {
        animation-delay: 8s;
    }
    .splash-scarf-6 {
        animation-delay: 10s;
    }

    /* Loading text */
    .splash-text {
        color: #ffb3ad;
        font-family: 'Quicksand', sans-serif;
        font-weight: 700;
        letter-spacing: 0.2em;
        font-size: 1.25rem;
        animation: splashPulseText 2s ease-in-out infinite;
        margin-top: 3rem;
        text-align: center;
        z-index: 2;
    }

    @keyframes splashPulseText {
        0%,
        100% {
            opacity: 0.4;
            transform: scale(0.98);
        }
        50% {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
