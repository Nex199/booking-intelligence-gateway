<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

<div class="mt-5">
    <div class="mb-3">
        <button id='btn-refresh' class='btn btn-primary'>Обновить данные</button>
    </div>
        
    <div class="row" id="deals-container">

    </div>
</div>

<script>
    const container = document.getElementById('deals-container');
    document.getElementById('btn-refresh').addEventListener('click', function(){
        container.innerHTML = '';
        fetch('/api/deals').then(response => response.json()).then(data => {data.forEach(deal =>{
            container.insertAdjacentHTML('beforeend', `<div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h2>${deal.title}</h2>
                            <p>id:${deal.id}</p>
                            <p>${deal.status}</p>
                        </div>
                    </div>
                </div>`)
        })})
    });
</script>

    
</body>
</html>