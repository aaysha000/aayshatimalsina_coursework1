<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Canceled</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f7ed; /* Subtle light green background */
            color: #2c6e49; /* Dark green text for a clean aesthetic */
        }

        h1 {
            font-size: 2.5rem;
            color: #d9534f; /* Red color to indicate cancellation */
            margin-bottom: 20px;
        }

        .message {
            font-size: 1.25rem;
            margin-bottom: 30px;
            max-width: 600px;
            text-align: center;
            line-height: 1.6;
        }

        .buttons-container {
            display: flex;
            gap: 20px;
        }

        .button {
            background-color: #4CAF50; /* Green background for buttons */
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 30px; /* Rounded buttons for a modern look */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Subtle shadow for depth */
            transition: all 0.3s ease;
        }

        .button:hover {
            background-color: #367c39; /* Darker green on hover */
            transform: translateY(-3px); /* Slight upward motion */
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        .button:active {
            transform: translateY(1px); /* Pressed effect */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Reset shadow */
        }
    </style>
</head>
<body>
    <h1>Payment Canceled</h1>
    <p class="message">Your payment has been canceled. If this was a mistake, you can try again or contact us for assistance.</p>
    <div class="buttons-container">
        <a href="cart.php" class="button">Back to Cart</a>
        <a href="index.php" class="button">Back to Home</a>
    </div>
</body>
</html>
