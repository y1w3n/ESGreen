<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Review - Processed Data</title>
</head>
<body>
    <h1>Processed Data for Admin Review</h1>
	<img src="assets/images/esgreen.png" alt="Logo 1" class="logo1">
    <div class="logo-container">
        <img src="assets/images/sunway-logo.png" alt="Logo 1" class="logo">
    </div>
    <table>
        <thead>
            <tr>
                <th>Part</th>
                <th>Score</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody id="data-table-body">
        </tbody>
    </table>

    <script>
        fetch('8_hc.php')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('data-table-body');
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${row.part}</td><td>${row.score}</td><td>${row.category}</td>`;
                    tableBody.appendChild(tr);
                });
            });
    </script>
</body>
</html>
