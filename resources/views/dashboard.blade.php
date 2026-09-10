<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kanal Kalimantan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen p-8">
 @include('components.navbar')
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Admin (Sementara)</h1>
            <button onclick="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm font-semibold transition">
                Logout
            </button>
        </div>

        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700 font-medium">
                <i class="fa-solid fa-circle-check mr-2"></i> Login Berhasil! Kamu terhubung dengan API Backend.
            </p>
        </div>

        <!-- Info User dari LocalStorage -->
        <div class="space-y-3 bg-gray-50 p-4 rounded border text-sm">
            <p><strong>Nama User:</strong> <span id="userName" class="text-gray-600">-</span></p>
            <p><strong>Username:</strong> <span id="userUsername" class="text-gray-600">-</span></p>
            <p><strong>Bearer Token:</strong></p>
            <textarea id="userToken" readonly class="w-full h-20 p-2 text-xs bg-gray-200 border rounded font-mono text-gray-700" wrap="all"></textarea>
        </div>
    </div>

    <script>
        // Ambil data dari localStorage
        const token = localStorage.getItem('access_token');
        const user = JSON.parse(localStorage.getItem('user_data') || '{}');

        // Jika tidak ada token, paksa balik ke login
        if (!token) {
            alert('Kamu belum login!');
            window.location.href = '/login';
        } else {
            document.getElementById('userName').innerText = user.name || '-';
            document.getElementById('userUsername').innerText = user.username || '-';
            document.getElementById('userToken').value = token;
        }

        function logout() {
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_data');
            window.location.href = '/login';
        }
    </script>
</body>
</html>