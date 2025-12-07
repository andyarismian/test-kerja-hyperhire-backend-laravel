<html>
<body>
    <p>Admin,</p>
    <p>The person <strong>{{ $person->name }}</strong> (ID: {{ $person->id }}) has reached {{ $person->likes_count }} likes.</p>
    <p>Location: {{ $person->location }}</p>
    <p>Regards,<br/>System</p>
</body>
</html>
