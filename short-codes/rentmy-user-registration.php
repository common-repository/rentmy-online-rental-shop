<?php
function rentmy_user_ragistration_shortcode(){

    $error = [];
    $sucess = '';
    if(isset($_POST['submit'])){
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $user_name = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $user_type = $_POST['user_type'];
        $data = $_POST;
        
        if($first_name==''){
            $error[] = 'First name is required';
        }
        if($email==''){
            $error[] = 'Email is required';
        }
        if($user_name==''){
            $error[] = 'Username is required';
        }
        if($user_type == ''){
            $error[] = 'User Type is required';
        }
        if($password==''){
            $error[] = 'Password is required';
           
        }else{
            if($confirm_password==''){
                $error[] = 'Confirm password is required';
            }else{
                if($confirm_password != $password){
                    $error[] = "Password doesn't match";
                }
            }
        }

        if(count($error) <= 0){
            $userdata = array(
                'first_name' => $first_name,
                'user_login' => $user_name,
                'user_pass'	=> $password,
                'user_email' => $email,
                'role'		=> 'customer'
            );
    
            $user_id = wp_insert_user($userdata);

            if($user_id){
                $params = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'password' => trim($password),
                    'email' => $email,
                    'user_type_id'=> 1
                ];
                $userObj = new RentMy_User();
                $user = $userObj->register($params);
                    $sucess = 'Customer has been saved successfully';
            }
            $data = [];
        }
    }


    
    ob_start();
    user_registration_template($error, $data, $sucess);
    
}
add_shortcode( 'rentmy-user-registration', 'rentmy_user_ragistration_shortcode');

function user_registration_template($error = [], $userdata = [], $success = '')
{
    $roles = [
        [
            'role' => 'customer',
            'display_name' => 'Customer',
            'type' => 1
        ]
//        (object)[
//            'role' => 'cashier',
//            'display_name' => 'Cashier'
//        ],
//        (object)[
//            'role' => 'rentmy-user',
//            'display_name' => 'Rentmy User'
//        ],
//        (object)[
//            'role' => 'admin',
//            'display_name' => 'Admin'
//        ]
    ];
    ?>


<section class="rentmy-login-register-content rentmy-register-content rentmy-plugin-manincontent">
        <div class="rentmy-login-register-content-overley">
            <div class="container-fluid h-100">
                <div class="row align-items-center justity-content-center h-100">
                    <div class="login-container">
                        <div class="login-container-body">
                        <?php if(count($error) > 0){?>
                            <ul class="rentmy-error">
                            <?php foreach($error as $er){?>
                                <li><?php echo $er;?></li>
                                <?php } ?>
                            </ul>
                            <?php } ?>

                            <?php if($success != ''){echo $success;} ?>

                            
                            <div class="login-from">
                                <form class="login" method="post" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" placeholder="First Name" class="form-control" name="first_name" value="<?php echo isset($userdata['first_name'])?$userdata['first_name']:'';?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" placeholder="Last Name" class="form-control" name="last_name" value="<?php echo isset($userdata['last_name'])?$userdata['last_name']:'';?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" placeholder="User name" class="form-control" name="username" value="<?php echo isset($userdata['username'])?$userdata['username']:'';?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" placeholder="Email" class="form-control" name="email" value="<?php echo isset($userdata['email'])?$userdata['email']:'';?>">
                                            </div>
                                        </div>
                                      
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Password</label>
                                                <input type="password" placeholder="Password" class="form-control" name="password">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Confirm Password</label>
                                                <input type="password" placeholder="Confirm Password" class="form-control" name="confirm_password">
                                            </div>
                                        </div>
                                        <?php if(!empty($roles)){?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>User Type</label>

                                                <select class="form-control" name="user_type">
                                                    <?php foreach ($roles as $role){?>
                                                    <option value="<?php echo $role['type']; ?>"><?php echo $role['display_name']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        
                                    <div class="form-group col-md-12">
                                        <button type="submit" class="btn sub-btn btn-brand" name="submit">Register</button>
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