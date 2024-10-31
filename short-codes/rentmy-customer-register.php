<?php
//short code for product details of a product
function rentmy_customer_register_shortcode()
{

    ob_start();
     rentmy_customer_register_template();

}

add_shortcode('rentmy-customer-register', 'rentmy_customer_register_shortcode');

function rentmy_customer_register_template()
{
?>

<section class="rentmy-login-register-content rentmy-register-content rentmy-plugin-manincontent">
        <div class="rentmy-login-register-content-overley">
            <div class="container-fluid h-100">
                <div class="row align-items-center justity-content-center h-100">
                    <div class="login-container">
                        <div class="login-container-body">
                            <div class="login-heading">Register</div>
                            <div class="login-from">
                                <form class="login">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" placeholder="First Name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" placeholder="Last Name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" placeholder="Email" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company Name</label>
                                                <input type="text" placeholder="Company Name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Contact Name</label>
                                                <input type="text" placeholder="Contact Name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="password" placeholder="Password" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" placeholder="Confirm Password" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Address Line1</label>
                                                <input type="text" placeholder="Address Line1" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Address Line2</label>
                                                <input type="text" placeholder="Address Line2" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Country</label>
                                                <select class="form-control"> 
                                                    <option>USA</option>
                                                    <option>UK</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" placeholder="City" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>State</label>
                                                <input type="text" placeholder="State" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Zipcode</label>
                                                <input type="text" placeholder="Zipcode" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn sub-btn">Register</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
}
?>
