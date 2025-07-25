<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="flex items-top justify-center min-h-screen bg-neutral-100 dark:bg-neutral-900 py-4 sm:pt-0">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 py-4 flex flex-col gap-2">
                <h1 class="text-3xl text-neutral-900 dark:text-neutral-100">синхронизация с google tables</h1>
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded hover:cursor-pointer" id="generate-records">Сгенерировать 1000 записей</button>
                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded hover:cursor-pointer" id="delete-records">Удалить все записи</button>
                <table class="table-auto w-full bg-neutral-100 dark:bg-neutral-700">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>title</th>
                            <th>status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr class="odd:bg-neutral-200 dark:odd:bg-neutral-600">
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->title }}</td>
                                <td>{{ $record->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </body>
    <script>
        const generateButton = document.getElementById('generate-records');
        generateButton.addEventListener('click', async () => {
            await getData();
        })

        async function getData() {
            try {
                const response = await fetch('/api/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        count: 1000
                    })
                });
                if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
                }

                const json = await response.json();
                const records = json.records;
                const table = document.querySelector('tbody');

                records.forEach(record => {
                    const row = document.createElement('tr');
                    row.classList.add('odd:bg-neutral-200', 'dark:odd:bg-neutral-600');
                    row.innerHTML = `
                        <td>${record.id}</td>
                        <td>${record.title}</td>
                        <td>${record.status}</td>
                    `;
                    table.appendChild(row);
                });

            } catch (error) {
                console.error(error.message);
            }
        }

        const deleteButton = document.getElementById('delete-records');
        deleteButton.addEventListener('click', async () => {
            await deleteData();
        })

        async function deleteData() {
            try {
                const response = await fetch('/api/destroy-all', {
                    method: 'DELETE'
                });
                if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
                }
                const table = document.querySelector('tbody');
                table.innerHTML = '';
            } catch (error) {
                console.error(error.message);
            }
        }

    </script>
</html>
