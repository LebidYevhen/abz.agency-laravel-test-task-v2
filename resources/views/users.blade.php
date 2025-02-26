<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
</head>
<body>

<div class="container py-5">

    <form class="js-token-form">
        <button type="submit" class="btn btn-primary">Get Token</button>
    </form>

    <form class="js-user-form mt-3 mb-5" >
        <div class="js-error-message"></div>

        <div class="row mb-3">
            <div class="col-md-6 js-input-wrapper">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="form-control">
            </div>

            <div class="col-md-6 js-input-wrapper">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 js-input-wrapper">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>

            <div class="col-md-6 js-input-wrapper">
                <label for="position_id" class="form-label">Position</label>
                <select id="position_id" name="position_id" class="form-select js-position-select">
                    <option value="">Select Position</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 js-input-wrapper">
                <label for="photo" class="form-label">Photo</label>
                <input type="file" id="photo" name="photo" class="form-control">
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Create User</button>
        </div>
    </form>

    <table class="table js-users-table">
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Phone</th>
            <th scope="col">Position</th>
            <th scope="col">Photo</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <button class="btn btn-primary js-show-more">Show More</button>
</div>

<script>
    let currentPage = 1;
    const usersPerPage = 6;
    const tableBody = document.querySelector('.js-users-table tbody');
    const showMoreBtn = document.querySelector('.js-show-more');
    const userForm = document.querySelector('.js-user-form');
    const positionSelect = document.querySelector('.js-position-select');
    const errorMessage = document.querySelector('.js-error-message');
    const tokenForm = document.querySelector('.js-token-form');

    async function getUsers(page = 1, count = usersPerPage) {
        try {
            const response = await fetch(`/api/v1/users?page=${page}&count=${count}`);
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                displayUsers(data.users);
                currentPage++;
                if (!data.links.next_url) {
                    showMoreBtn.style.display = 'none';
                }
            } else {
                console.error('Failed to fetch users:', data.message);
            }
        } catch (error) {
            console.error(error.message);
        }
    }

    function displayUsers(users) {
        users.forEach(user => appendUserToTable(user, false));
    }

    function appendUserToTable(user, isNew = true) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.phone}</td>
            <td>${user.position}</td>
            <td><img src="${user.photo}" alt="User Photo" width="70"></td>
        `;

        if (isNew) {
            tableBody.prepend(row);
        } else {
            tableBody.appendChild(row);
        }
    }

    async function loadPositions() {
        try {
            const response = await fetch('/api/v1/positions');
            if (!response.ok) throw new Error(`Response status: ${response.status}`);

            const data = await response.json();
            if (data.success) {
                data.positions.forEach(position => {
                    const option = document.createElement('option');
                    option.value = position.id;
                    option.textContent = position.name;
                    positionSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading positions:', error.message);
        }
    }

    userForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const authToken = localStorage.getItem('authToken');
        if (!authToken) {
            alert('Token not found! Please get the token first.');
            return;
        }

        const formData = new FormData(userForm);

        try {
            const response = await fetch('/api/v1/users', {
                method: 'POST',
                body: formData,
                headers: {
                    'Authorization': `Bearer ${authToken}`,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            clearMessages();

            if (data.success) {
                userForm.reset();
                const newUser = await fetchUserById(data.user_id);
                appendUserToTable(newUser, true);
                showMessage(data.message, 'success');
            } else {
                displayErrors(data);
            }
        } catch (error) {
            console.error('Error adding user:', error.message);
        }
    });

    function displayErrors(data) {
        clearMessages();

        if (data.message) {
            showMessage(data.message, 'danger');
        }

        Object.entries(data.errors).forEach(([field, messages]) => {
            const inputField = document.querySelector(`[name="${field}"]`);
            if (inputField) {
                const parentDiv = inputField.closest('.js-input-wrapper');
                if (parentDiv) {
                    let errorSpan = document.createElement('span');
                    errorSpan.classList.add('error-message', 'text-danger', 'mt-1', 'd-block');
                    parentDiv.appendChild(errorSpan);
                    errorSpan.textContent = messages[0];
                }
            }
        });
    }

    function clearMessages() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelector('.js-error-message').innerHTML = '';
    }

    function showMessage(message, type) {
        errorMessage.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }

    async function fetchUserById(userId) {
        try {
            const response = await fetch(`/api/v1/users/${userId}`);
            const userResponse = await response.json();

            if (userResponse.success) {
                return userResponse.user;
            } else {
                showMessage(userResponse.message, 'danger');
            }
        } catch (error) {
            console.error('Error fetching user:', error.message);
        }
        return null;
    }

    tokenForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        try {
            const response = await fetch('/api/v1/token', {
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (response.ok) {
                localStorage.setItem('authToken', data.token);
                await navigator.clipboard.writeText(data.token)
                alert('Token copied to clipboard and saved!');
            } else {
                console.error('Failed to get token:', data.message);
            }
        } catch (error) {
            console.error('Error:', error.message);
        }
    });

    showMoreBtn.addEventListener('click', () => {
        getUsers(currentPage);
    });

    loadPositions();
    getUsers();
</script>

</body>
</html>