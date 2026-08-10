<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Listing Submitted</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="margin-bottom: 16px;">New Listing Submitted</h2>
    <p>
        A new listing for <strong>{{ $productName }}</strong> was submitted by
        <strong>{{ $createdBy }}</strong>.
    </p>
    <p>Its verification status is <strong>pending</strong> and it requires approval.</p>
</body>
</html>
