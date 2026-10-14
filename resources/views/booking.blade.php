<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="webhook-secret" content="{{ config('services.mock_service.secret') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>
</head>
<body> 
    <div class="container mt-5">
        <h1>Отель Элеон - Бронирование</h1>
        <div class="card p-4" >
            <form id="booking-form">
                <div class="d-flex gap-3">
                    <div class="mb-3 w-50">
                        <label class="form-label">Имя</label>
                        <input placeholder="Иван" name="guest_name" class="form-control">
                    </div>
                    

                    <div class="mb-3 w-50">
                        <label class="form-label">Номер</label>
                        <input name="guest_phone" placeholder="81234567890" class="form-control">
                    </div>
                
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" placeholder="ivan@gmail.com" name="guest_email" type="email">
                </div>

                <div class="mb-3">
                    <label class="form-label">Пожелания</label>
                    <textarea class="form-control" name="guest_preferences" placeholder="2 взрослых 1 ребенок, заезд с 15 по 20 октября, номер с видом на море"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Отправить заявку</button>
                
                <div class="spinner-border d-none" id="spinner">

                </div>
            </form>
        </div>
    </div>
    
<script>
    const spin = document.querySelector('#spinner');
    
    const form = document.querySelector('#booking-form');

    form.addEventListener('submit', async function(event) {
        spin.classList.remove('d-none');
        event.preventDefault();

        const response = await fetch('/api/webhooks/mock-booking', {
            'method': 'POST',
            'headers': {
                'Content-type': 'application/json',
                'Accept': 'application/json',
                'X-Webhook-Secret': document.querySelector('meta[name="webhook-secret"]').content,
            },
            'body': JSON.stringify({
                'event_type': 'lead.created_webform',
                'guest_preferences': form.elements.guest_preferences.value,
                'guest_name': form.elements.guest_name.value,
                'guest_email': form.elements.guest_email.value,
                'event_id': crypto.randomUUID(),
                'guest_phone': form.elements.guest_phone.value,
                'external_id': 'web_' + crypto.randomUUID(),
                'source': 'webform'
            })
        });
        spin.classList.add('d-none');
        if (response.ok) {
            console.log('Успешно');
            console.log('Статус: ' + response.status);
            alert('Успешно');
            form.reset();
        }
        else {
            console.log('Ошибка');
            console.log('Статус: ' + response.status);
            alert('Ошибка, попробуйте ещё');
        }
    });

    
</script>
</body>
</html>