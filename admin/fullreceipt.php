<html>
<head>
<title>Full receipt generator</title>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
        function validateNumbers() {
            var vehicle_price = parseFloat(document.getElementById("vehicle_price").value);
            var amount_paid = parseFloat(document.getElementById("amount_paid").value);
			var payment_type = document.getElementById("payment_type").value;

            if (amount_paid > vehicle_price) {
                alert("The amount paid cannot be greater than the actual price");
                return false; // Validation failed
            } else if(payment_type === "full" && amount_paid < vehicle_price){
				alert("The amount paid cannot be lesser than vehicle price for a full payment, change the payment type if this is installment payment");
                return false; // Validation failed
			}
			else
			{
                return true;
            }
        }
    </script>
</head>


<body>
<form method="POST" action="receipt_processor.php" onsubmit="return validateNumbers()">
	<fieldset>
	<legend>Customer Information</legend>
	Customer Fullname *:  <input type="text" name="customer_name" placeholder="Enter customer fullname" required /><br />
	Customer Address *:  <textarea name="customer_address" placeholder="Enter Customer Full address" required></textarea><br />
	Customer Phone number *:  <input type="text" name="customer_phone" placeholder="Enter customer phone number" required /><br />
	Customer Email(optional):  <input type="email" name="customer_email" placeholder="Enter customer email" />
	</fieldset>
	<br /><br />
	<fieldset>
	<legend>Vehicle Information</legend>
	Make: * <input type="text" name="vehicle_make" placeholder="Enter vehicle make" required /><br />
	Model: *  <input type="text" name="vehicle_model" placeholder="Enter vehicle model"required><br />
	Year:  *<input type="text" name="vehicle_year" placeholder="Enter vehicle year" required /><br />
	Chasis no: * <input type="text" name="vehicle_chasis" placeholder="Enter vehicle chasis" required /><br />
	Color: * <input type="text" name="vehicle_color" placeholder="Enter vehicle color" required /><br />
	Actual Vehicle Price(=N=) (optional):  <input type="number" id="vehicle_price" name="vehicle_price" placeholder="Enter vehicle price" /><br />
	Additional Vehicle information (optional):  <input type="text" name="add_vehicle" placeholder="Enter any additional vehicle information" /><br />
	</fieldset>
	
	<br /><br />
	<fieldset>
	<legend>Transaction details</legend>
	Payment type (Full or installment):*  <select id="payment_type" name="payment_type" required>
					<option value="" disabled selected hidden>Select payment type</option>
					<option value="full">Full payment</option>
					<option value="installment">Installment payment</option>
					</select><br />
	Payment method (bank transfer, cheque, cash etc.):*  <input type="text" name="payment_method" placeholder="Enter payment method" required />  <br />
	Payment reference(optional):  <input type="text" name="payment_reference" placeholder="Enter payment reference" /><br />
	Amount paid(=N=):* <input type="number" id="amount_paid" name="amount_paid" placeholder="Enter amount paid" required /><br />
	Date of payment:*  <input type="date" name="payment_date" placeholder="Enter payment date" required /><br />
	Additional Payment information (optional):  <input type="text" name="add_payment" placeholder="Enter any additional payment information" /><br />
	
	</fieldset>
	<br />
	<!--div class="g-recaptcha" data-sitekey="6LevyMMpAAAAAHbKpF8WAYLlWOXeCwc3v8lZkR2y"></div-->
	<br />
	<input type="submit" name="receipt_submit">
</form>

</body>
</html>