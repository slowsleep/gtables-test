@extends('layouts.app')

@section('content')
<div class="flex flex-col min-w-3xl">
    <h1 class="text-2xl mt-4 mb-4 text-neutral-900 dark:text-neutral-300">Fetch Google Sheet Data</h1>

    <div class="flex flex-row justify-between items-center gap-2">
        <span class="text-neutral-900 dark:text-neutral-300" id="progress-count">[0/0]</span>
        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5 flex-1">
            <div id="progressbar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
        </div>
        <span class="text-neutral-900 dark:text-neutral-300" id="progress-percentage">0%</span>
    </div>

    <pre class="overflow-y-auto bg-neutral-200 dark:bg-neutral-800 rounded p-4 text-sm text-neutral-900 dark:text-neutral-300"
     style="height: calc(100vh - 100px);" id="fetch"></pre>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sheetData = @json($sheetData);
        const pre = document.getElementById('fetch');
        const progressCount = document.getElementById('progress-count');
        const progressBar = document.getElementById('progressbar');
        const progressPercentage = document.getElementById('progress-percentage');
        const totalRows = sheetData.length;
        let currentRow = 0;
        const delay = 50; // 50ms между строками

        function displayNextRow() {
            // Завершаем анимацию
            if (currentRow >= totalRows) {
                progressCount.textContent = `[${currentRow}/${totalRows}]`;
                progressBar.style.width = '100%';
                progressPercentage.textContent = '100%';
                return;
            }

            const row = sheetData[currentRow];
            const rowHtml = `ID: <span class="text-orange-500">${row[0] ?? 'N/A'}</span> | Комментарий: <span class="text-green-500">${row[5] ?? ''}</span><br>`;

            // Добавляем строку
            pre.innerHTML += rowHtml;

            // Прокручиваем вниз
            pre.scrollTop = pre.scrollHeight;

            // Обновляем значение ProgressBar
            progressCount.textContent = `[${currentRow + 1}/${totalRows}]`;
            const progress = Math.round((currentRow + 1) / totalRows * 100);
            progressBar.style.width = `${progress}%`;
            progressPercentage.textContent = `${progress}%`;

            currentRow++;

            // Рекурсивно вызываем следующую строку с задержкой
            setTimeout(displayNextRow, delay);
        }

        // Начинаем вывод
        displayNextRow();
    });
</script>
@endpush
