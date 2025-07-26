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
                <form class="flex flex-col gap-2 bg-neutral-700 dark:bg-neutral-800 p-4 text-neutral-100" id="create-record-form">
                    <h2>Создать запись</h2>
                    <div class="flex flex-row broder-white-1">
                        <label class="w-1/4" for="title">title</label>
                        <input class="border border-gray-500 w-full" type="text" name="title" id="title">
                    </div>
                    <div class="flex flex-row broder-white-1">
                        <label class="w-1/4" for="status">status</label>
                        <select class="border border-gray-500 w-full" name="status" id="status">
                            <option value="Allowed">Allowed</option>
                            <option value="Prohibited">Prohibited</option>
                        </select>
                    </div>
                    <input type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded hover:cursor-pointer" value="Создать" />
                </form>
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
                            <tr class="odd:bg-neutral-200 dark:odd:bg-neutral-600" data-key="{{ $record->id }}">
                                <td data-record-id="{{$record->id}}">{{ $record->id }}</td>
                                <td>{{ $record->title }}</td>
                                <td>{{ $record->status }}</td>
                                <td><button
                                    class="edit-record bg-blue-400 hover:bg-blue-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
                                    data-record-id="{{$record->id}}">редактировать</button></td>
                                <td><button
                                    class="delete-record bg-red-400 hover:bg-red-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
                                    data-record-id="{{$record->id}}">удалить</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div id="modal" class="fixed left-[50%] top-[50%] transform -translate-x-1/2 -translate-y-1/2 z-5 bg-neutral-100 dark:bg-neutral-900 p-3 text-neutral-900 dark:text-neutral-100 border-1 border-blue-500 hidden">
            <form name="edit-form" class="flex flex-col">
                <h1>Редактирование записи</h1>
                <p>id: <span id="modal-record-id"></span></p>
                <div class="flex flex-row justify-between">
                    <p>title: </p>
                    <input class="border border-gray-500" type="text" placeholder="title" name="title" id="modal-record-title" />
                </div>
                <div class="flex flex-row gap-2">
                    <p>status: </p>
                    <select class="border border-gray-500" name="status" id="modal-record-status">
                        <option value="Allowed">Allowed</option>
                        <option value="Prohibited">Prohibited</option>
                    </select>
                </div>
                <div class="flex flex-row justify-between">
                    <button type="submit" name="save" class="bg-green-500 hover:bg-green-700 text-white font-bold p-1 rounded hover:cursor-pointer">сохранить</button>
                    <button type="button" name="cancel" class="bg-gray-500 hover:bg-gray-700 text-white font-bold p-1 rounded hover:cursor-pointer">отмена</button>
                </div>
            </form>
        </div>
        <div class="fixed top-0 left-0 w-full h-full bg-black opacity-50 z-4 hidden" id="overlay"></div>
    </body>
    <script src="{{ asset('js/index.js') }}"></script>
</html>
