// cоздание новой записи
const createRecordForm = document.getElementById('create-record-form');
createRecordForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        const response = await fetch('/api/record', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: createRecordForm.title.value,
                status: createRecordForm.status.value
            })
        })
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
        const json = await response.json();
        const record = json.record;
        const table = document.querySelector('tbody');
        const row = document.createElement('tr');
        row.classList.add('odd:bg-neutral-200', 'dark:odd:bg-neutral-600');
        row.innerHTML = `
            <td>${record.id}</td>
            <td>${record.title}</td>
            <td>${record.status}</td>
            <td><button
            class="edit-record bg-blue-400 hover:bg-blue-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
            data-record-id="${record.id}">редактировать</button></td>
            <td><button
            class="delete-record bg-red-400 hover:bg-red-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
            data-record-id="${record.id}">удалить</button></td>
        `;
        table.appendChild(row);
    } catch (error) {
        console.error(error.message);
    }

})

// генерирование 1000 записей
const generateButton = document.getElementById('generate-records');
generateButton.addEventListener('click', async () => {
    await getData();
})

async function getData() {
    try {
        const response = await fetch('/api/records/generate', {
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
                <td><button
                class="edit-record bg-blue-400 hover:bg-blue-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
                data-record-id="${record.id}">редактировать</button></td>
                <td><button
                class="delete-record bg-red-400 hover:bg-red-800 text-white font-bold py-1 px-2 m-1 rounded hover:cursor-pointer"
                data-record-id="${record.id}">удалить</button></td>
            `;
            table.appendChild(row);
        });

    } catch (error) {
        console.error(error.message);
    }
}


// удаление всех записей
const deleteButton = document.getElementById('delete-records');
deleteButton.addEventListener('click', async () => {
    await deleteData();
})

async function deleteData() {
    try {
        const response = await fetch('/api/records/destroy-all', {
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

// открытие модального окна с данными записи для редактирования записи
async function openEditModal(recordId) {
    modal.classList.remove('hidden');
    overlay.classList.remove('hidden');
    let editForm = modal.querySelector("form[name='edit-form']");
    let textId = editForm.querySelector("#modal-record-id");
    let inputTitle = editForm.querySelector("#modal-record-title");
    let inputStatus = editForm.querySelector("#modal-record-status");

    const response = await fetch('/api/record/' + recordId);

    if (response.ok) {
        let data = await response.json();
        let record = data.record;
        textId.innerHTML = record.id.toString();
        inputTitle.value = record.title;
        inputStatus.value = record.status;
    } else {
        alert(response.statusText);
    }

    let saveButton = editForm.querySelector("button[name='save']");
    let cancelButton = editForm.querySelector("button[name='cancel']");

    // убираем старые обработчики (если повторно откроем модалку)
    const saveClone = saveButton.cloneNode(true);
    saveButton.parentNode.replaceChild(saveClone, saveButton);

    const cancelClone = cancelButton.cloneNode(true);
    cancelButton.parentNode.replaceChild(cancelClone, cancelButton);

    cancelClone.addEventListener('click', () => {
        modal.classList.add('hidden');
        overlay.classList.add('hidden');
    });

    saveClone.addEventListener('click', async (event) => {
        event.preventDefault();

        const response = await fetch('/api/record/' + recordId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: inputTitle.value,
                status: inputStatus.value
            })
        });

        if (response.ok) {
            modal.classList.add('hidden');
            overlay.classList.add('hidden');
            location.reload();
        } else {
            alert(response.statusText);
        }
    });
}

// обработка кликов по таблице
// для обработки кнопок редактирования и удаления
const tableBody = document.querySelector('tbody');

tableBody.addEventListener('click', async (event) => {
    const target = event.target;

    // редактирование записи
    if (target.classList.contains('edit-record')) {
        const recordId = target.getAttribute('data-record-id');
        // показ модалки + загрузка данных
        await openEditModal(recordId);
    }

    // удаление записи
    if (target.classList.contains('delete-record')) {
        const recordId = target.getAttribute('data-record-id');
        const isDelete = confirm("Вы уверены, что хотите удалить запись?");
        if (isDelete) {
            await deleteRecord(recordId);
        }
    }
});

async function deleteRecord(recordId) {
    try {
        const response = await fetch('/api/record/' + recordId, {
            method: 'DELETE'
        });
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
        location.reload();
    } catch (error) {
        console.error(error.message);
    }
}
