<?php
//short code for product details of a product
function rentmy_customer_profile_shortcode()
{
    ob_start();
    if ((!empty($_SESSION['customer_info']) || !empty($_COOKIE['rentmy_customer_info']))) {
        $rentmy_config = new RentMy_Config();
        $store_content = $rentmy_config->store_contents();

        $GLOBALS['store_config'] = get_option('rentmy_config');
        $GLOBALS['store_text'] = [];
        if (!empty($store_content)) {
            $GLOBALS['store_text'] = $store_content[0]['contents'];
        }
        rentmy_customer_profile_template();
    }

    add_action('wp_footer', 'rentmy_customer_profile_scripts');
    return ob_get_clean();

}


function rentmy_customer_profile_scripts() {
?>
<script>
var rentmy_plugin_base_url = "<?php echo plugins_url('', RENTMY_PLUGIN_FILE) ?>";
var rm_countries = <?php echo json_encode((new RentMy_Config())->countries(), true); ?>;
var customer_info = <?php echo json_encode((new RentMy_Customer())->getCustomer()); ?>;
var store_content = <?php echo json_encode($GLOBALS['store_text']); ?>;

</script>

<script>
const customerBus = new Vue();
var vapp = new Vue({
    components:{
        'customer-profile': window.httpVueLoader('<?php echo plugins_url('assets/js/components/customer/Profile.vue', RENTMY_PLUGIN_FILE); ?>'),
        'customer-password-reset': window.httpVueLoader('<?php echo plugins_url('assets/js/components/customer/PasswordReset.vue', RENTMY_PLUGIN_FILE); ?>'),
        'customer-change-avatar': window.httpVueLoader('<?php echo plugins_url('assets/js/components/customer/ChangeAvatar.vue', RENTMY_PLUGIN_FILE); ?>'),
        'customer-order-history': window.httpVueLoader('<?php echo plugins_url('assets/js/components/customer/OrderHistory.vue', RENTMY_PLUGIN_FILE); ?>'),
        'customer-order-summary': window.httpVueLoader('<?php echo plugins_url('assets/js/components/customer/OrderSummary.vue', RENTMY_PLUGIN_FILE); ?>'),
    },
    el:'#rm-customer-profile',
    data(){
        return{
            is_active_component:{
                customer_password_reset: false,
                customer_change_avatar: false,
                customer_order_history: false,
                customer_order_summary: false,
            },
            selected_order_id: '',
            loading: true,
            customer_info: customer_info,
            order_status:[]
        }
    },
    methods:{
        mountComponent: function(name){
            let vm = this;
            vm.is_active_component[name] = true;
            if(vm.is_active_component.customer_order_history){
                vm.is_active_component.customer_order_summary = false;
            }

        },
        getOrderStatus: function(){
            let vm = this;
            let url = rentmy_ajax_object.ajaxurl;
            let data = new FormData();
            data.set("action_type", "get_order_status");
            data.set("action", "rentmy_options");
            axios.post(url, data).then(function(response){
                vm.order_status = response.data.data;
            });
        }
    },
    created: function(){
        let vm = this;
        customerBus.$on('getCustomerProfile', (data)=>{
            vm.loading = data;
        });
        customerBus.$on('getCustomerOrder', (data)=>{
            vm.loading = data;
        });
        this.getOrderStatus();
    }

});

</script>


<?php
};

add_shortcode('rentmy-customer-profile', 'rentmy_customer_profile_shortcode');

function rentmy_customer_profile_template()
{
    $store_config = [];
    if (!empty($_SESSION['rentmy_config'])) {
        $store_config = $_SESSION['rentmy_config'];
    }
?>

<script>
var rentmy_plugin_base_url = "<?php echo plugins_url('', RENTMY_PLUGIN_FILE) ?>";
var rm_countries = <?php echo json_encode((new RentMy_Config())->countries(), true); ?>;
var customer_info = <?php echo json_encode((new RentMy_Customer())->getCustomer()); ?>;
var store_content = <?php echo json_encode($GLOBALS['store_text']); ?>;
var store_config = <?php echo json_encode($store_config, true); ?>;
</script>
<section class="rentmy-profile-section pb-5" id="rm-customer-profile">
        <div class="container-fluid" v-if="Object.keys(customer_info).length != 0">
            <div class="row">
                <div class="col-md-12 p-0">
                    <div class="rentmy-profile-section-inner">
                        <div class="profile-rowarea">
                            <!-- <div class="userleft-side">
                                <div class="rentmy-profile-leftside">
                                    <div class="rentmy-profile-leftside-body">
                                        <img alt="customer profile" class="img-fluid" src="" />
                                        <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                            <li>
                                                <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#rentmy-clientprofile-profile" role="tab" aria-controls="v-pills-home" aria-selected="true">
                                                    <i class="fa fa-user"></i>Profile
                                                </a>
                                            </li>
                                            <li v-on:click="mountComponent('customer_password_reset')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-password" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-unlock"></i>Change Password
                                                </a>
                                            </li>
                                            <li v-on:click="mountComponent('customer_change_avatar')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-avatar" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-image"></i>Change Avatar
                                                </a>
                                            </li>
                                            <li v-on:click="mountComponent('customer_order_history')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-history" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-history"></i>Orders History
                                                </a>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            </div> -->



                            <div class="userright-side">
                                <div class="customerprofile-content">
                            <ul class="nav flex-column nav-pills customer-profile-tab" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                            <li>
                                                <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#rentmy-clientprofile-profile" role="tab" aria-controls="v-pills-home" aria-selected="true">
                                                    <i class="fa fa-user"></i><?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_profile'])?$GLOBALS['store_text']['customer_portal']['lbl_profile']:'Profile'?>
                                                </a>
                                            </li>
                                            <li v-on:click="mountComponent('customer_password_reset')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-password" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-unlock"></i><?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_change_password'])?$GLOBALS['store_text']['customer_portal']['lbl_change_password']:'Change Password'?>
                                                </a>
                                            </li>
                                            <!-- <li v-on:click="mountComponent('customer_change_avatar')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-avatar" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-image"></i>Change Avatar
                                                </a>
                                            </li> -->
                                            <li v-on:click="mountComponent('customer_order_history')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-history" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-history"></i><?php echo !empty($GLOBALS['store_text']['customer_portal']['lbl_order_history'])?$GLOBALS['store_text']['customer_portal']['lbl_order_history']:'Orders History'?>
                                                </a>
                                            </li>
                                            <!-- <li v-on:click="mountComponent('customer_order_summary')">
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-orderdetails" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-history"></i>Orders Details
                                                </a>
                                            </li> -->
                                            <!-- <li>
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-jobs" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-briefcase"></i>Jobs
                                                </a>
                                            </li>
                                            <li>
                                                <a class="nav-link" id="v-pills-profile-tab" data-toggle="pill" href="#rentmy-clientprofile-message" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                                    <i class="fa fa-send"></i>Send Message
                                                </a>
                                            </li> -->
                                        </ul>
                            <div class="tab-content" id="v-pills-tabContent" v-show="loading">
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                                <div class="tab-content" id="v-pills-tabContent" v-show="!loading"> 

                                
                                    <div class="tab-pane fade show active" id="rentmy-clientprofile-profile" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                     <customer-profile ref="profile"></customer-profile>
                                    </div>


                                    <div class="tab-pane fade" id="rentmy-clientprofile-password" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                       <customer-password-reset v-if="is_active_component.customer_password_reset"></customer-password-reset>

                                    </div>


                                    <!-- <div class="tab-pane fade" id="rentmy-clientprofile-avatar" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                        <customer-change-avatar v-if="is_active_component.customer_change_avatar"></customer-change-avatar>
                                    </div> -->



                                    <div class="tab-pane fade" id="rentmy-clientprofile-history" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                        <customer-order-history v-if="is_active_component.customer_order_history"></customer-order-history>
                                        <customer-order-summary v-if="is_active_component.customer_order_summary" :order_id="selected_order_id"></customer-order-summary>

                                    </div>
                                    <!-- <div class="tab-pane fade" id="rentmy-clientprofile-orderdetails" role="tabpanel" aria-labelledby="v-pills-settings-tab"> -->
                                        <!-- <customer-order-summary v-if="is_active_component.customer_order_summary"></customer-order-summary> -->

                                    </div>
                                    <!-- <div class="tab-pane fade" id="rentmy-clientprofile-jobs" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                        <div class="rentmy-profile-rightside w-100 p-0">
                                            <div class="profile-info-title">
                                                <h4>Job List</h4>
                                                <button class="btn btn-md theme-btn"><i class="la la-plus"></i>Create Job</button>
                                            </div>
                                            <div class="profile-body">
                                                <table class="table custom-table-responsive">
                                                    <tr>
                                                        <th>Job Name</th>
                                                        <th>Address</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">2020-03-25</td>
                                                        <td>2020-03-25</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">New job 2</td>
                                                        <td>New job 2</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">2020-03-24</td>
                                                        <td>2020-03-24</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">New job</td>
                                                        <td>New job</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">2020-03-23</td>
                                                        <td>2020-03-23</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">Test store 05 - first</td>
                                                        <td>Test store 05 - first</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="table-td-themecolor">Second Job</td>
                                                        <td>Second Job</td>
                                                        <td><span class="m-badge m-badge--wide m-badge--info" style="cursor: pointer;">Active</span></td>
                                                        <td>
                                                            <a title="Edit" class="mr-3"><i class="fa fa-edit"></i></a><a title="View details" class="mr-3"><i class="fa fa-eye"></i></a>
                                                            <a title="Delete job"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                    </div> -->
                                    <!-- <div class="tab-pane fade" id="rentmy-clientprofile-message" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                        <div class="rentmy-profile-rightside w-100 p-0">
                                            <div class="profile-info-title">
                                                <h4>Send Message</h4>
                                            </div>
                                            <div class="profile-body">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <form class="ng-untouched ng-pristine ng-invalid">
                                                            <div class="form-group">
                                                                <label class="mb-0">Feedback</label>
                                                                <textarea type="textarea" class="form-control"></textarea>
                                                            </div>
                                                            <div class="form-group"><button type="submit" class="btn theme-btn">Send</button></div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
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
