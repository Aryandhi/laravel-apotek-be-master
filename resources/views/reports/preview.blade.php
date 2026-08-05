<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview - {{ $title }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 16px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 14px;
            background: #ffffff;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .button:hover {
            background: #f3f4f6;
        }

        .button.primary {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }

        .button.primary:hover {
            background: #115e59;
        }

        .viewer {
            height: calc(100vh - 64px);
            padding: 12px;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #ffffff;
        }

        .fallback {
            margin: 16px;
            padding: 12px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .actions {
                width: 100%;
            }

            .actions .button,
            .actions form {
                flex: 1;
            }

            .actions .button,
            .actions button {
                width: 100%;
            }

            .viewer {
                height: calc(100vh - 120px);
            }
        }
    </style>
</head>
<body>
    <header class="toolbar">
        <h1 class="title">Preview {{ $title }}</h1>

        <div class="actions">
            <a class="button" href="{{ $downloadPdfUrl }}" rel="noopener">
                Unduh PDF
            </a>
            <button id="print-button" class="button primary" type="button">
                Print
            </button>
        </div>
    </header>

    <main class="viewer">
        <iframe
            id="report-frame"
            src="{{ $inlinePdfUrl }}"
            title="Preview laporan"
            loading="eager"
        >
            Browser ini tidak mendukung preview PDF di iframe.
        </iframe>
    </main>

    <div class="fallback" id="preview-fallback" hidden>
        Preview PDF tidak tersedia di browser ini. Gunakan tombol Unduh PDF, atau buka PDF langsung:
        <a href="{{ $inlinePdfUrl }}" target="_blank" rel="noopener">Buka PDF</a>
    </div>

    <script>
        (function () {
            var frame = document.getElementById('report-frame');
            var printButton = document.getElementById('print-button');
            var fallback = document.getElementById('preview-fallback');
            var inlinePdfUrl = @json($inlinePdfUrl);

            frame.addEventListener('error', function () {
                fallback.hidden = false;
            });

            printButton.addEventListener('click', function () {
                var frameWindow = frame && frame.contentWindow ? frame.contentWindow : null;

                try {
                    if (frameWindow && typeof frameWindow.print === 'function') {
                        frameWindow.focus();
                        frameWindow.print();
                        return;
                    }
                } catch (error) {
                    // Continue to fallback behavior below.
                }

                window.open(inlinePdfUrl, '_blank', 'noopener');
            });
        })();
    </script>
</body>
</html>
