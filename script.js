function submitCheck()
            {
                if (window.confirm('Are you sure you want to submit?') == true)
                {
                    printSignUp();
                }
                else
                {
                    return submit;
                }
            }
            
	        function printSignUp()
            {
                var firstName = document.getElementById ('firstName').value ;
                var lastName = document.getElementById ('lastName').value ;
                var email = document.getElementById ('email').value ;
                var pword = document.getElementById ('pword').value ;
                var state = document.getElementById ('state').value ;
                var zip = document.getElementById ('zip').value ;
                document.write ('<a href="index.html">Return to Home</a><br>' + 
                    'First name: ' + firstName + '<br> Last Name :' + lastName + 
                    '<br> Email: ' + email + '<br> Password: ' + pword + '<br> State: ' + 
                    state + '<br> Zip: ' + zip) ;
            }