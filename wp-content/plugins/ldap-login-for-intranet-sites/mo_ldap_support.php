<?php
include_once dirname( __FILE__ ) . '/add-on-framework.php';

function mo_ldap_local_support(){

    $current_user = wp_get_current_user();
    show_plugin_export_form();     //None display Pop up form
	if(get_option('mo_ldap_local_admin_email'))
		$admin_email = get_option('mo_ldap_local_admin_email');
	else
		$admin_email = $current_user->user_email;

    $server_url = get_option('mo_ldap_local_server_url') ? Mo_Ldap_Local_Util::decrypt(get_option('mo_ldap_local_server_url')) : '';
    ?>
	<div class="mo_ldap_support_layout" id="mo_ldap_support_layout_ldap" style="position: relative; line-height: 2">
		<h3>Contact Us</h3>
			<form name="f" method="post" action="">
				<div>Need any help? We can help you with configuring LDAP configuration. Just send us a query so we can help you.<br /><br />
                    </div>
				<div>
					<table class="mo_ldap_settings_table">
						<tr><td>
							<input type="email" class="mo_ldap_table_textbox" id="query_email" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter your email" required />
							</td>
						</tr>
						<tr><td>
							<input type="text" class="mo_ldap_table_textbox" name="query_phone" id="query_phone" value="<?php echo get_option('mo_ldap_local_admin_phone'); ?>" placeholder="Enter your phone"/>
							</td>
						</tr>
						<tr>
							<td>
								<textarea id="query" name="query" class="mo_ldap_settings_textarea" style="border-radius:4px;resize: vertical;width:100%" cols="52" rows="7"  placeholder="Write your query here" required ></textarea>
							</td>
						</tr>
					</table>
				</div>
				<input type="hidden" name="option" value="mo_ldap_login_send_query"/>
                <input type="hidden" id="server_url" value=<?php echo $server_url?>>
				<input type="button" onclick="popupForm()" name="send_query" id="send_query_support" value="Submit Query" style="margin-bottom:3%;" class="button button-primary button-large" />
			</form>
			<br />
	</div>
	<script>
		jQuery("#query_phone").intlTelInput();

        function popupForm()
        {
            var wpPointer = document.getElementById("wp-pointer-0");
            if(wpPointer != null){
                wpPointer.style.zIndex = "0";
            }
            var queryEmail = document.getElementById("query_email").value;
            var queryPhone = document.getElementById("query_phone").value;
            var queryValue = document.getElementById("query").value;
            var serverUrl =  document.getElementById("server_url").value;
            if(validateEmail()){
                if(queryValue.length>0)
                {
                    if(serverUrl.length>0){
                        var mo_ldap_modal = document.getElementById('ldapModal');
                        mo_ldap_modal.style.display = "block";
                        var span = document.getElementsByClassName("mo_ldap_close")[0];
                        document.getElementById("inner_form_email_id").value = queryEmail;
                        document.getElementById("inner_form_phone_id").value = queryPhone;
                        document.getElementById("inner_form_query_id").value = queryValue;
                        span.onclick = function () {
                            mo_ldap_modal.style.display = "none";
                        }
                        window.onclick = function (event) {
                            if (event.target == mo_ldap_modal) {
                                mo_ldap_modal.style.display = "none";
                            }
                        }
                    }
                    else
                    {
                        document.getElementById("inner_form_email_id").value = queryEmail;
                        document.getElementById("inner_form_phone_id").value = queryPhone;
                        document.getElementById("inner_form_query_id").value = queryValue;
                        document.getElementById('export_configuration_choice').value='';
                        document.getElementById('mo_ldap_export_pop_up').submit();
                    }
                }
                else
                {
                    alert("Query field cannot be empty!");
                }
            }
        }

        function validateEmail()
        {
            var email = document.getElementById('query_email');
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email.value))
            {
                return (true)
            }
            else if(email.value.length == 0){
                alert("Please enter your email address!")
                return (false)
            }
            else
            {
                alert("You have entered an invalid email address!")
                return (false)
            }

        }

	</script>
<?php
}
function show_plugin_export_form(){
    wp_enqueue_style('mo_ldap_admin_plugins_page_style', plugins_url('includes/css/mo_ldap_plugins_page.css', __FILE__));
    ?>
     </head>
   <body>

   <!-- The Modal -->
   <div id="ldapModal" class="mo_ldap_modal" style="margin-left: 150px;">
       <div class="moldap-modal-contatiner-contact-us" style="color:black"></div>
       <!-- Modal content -->
       <div class="mo_ldap_modal_content_configuration_support">
           <span class="mo_ldap_close">&times;</span>
           <h3>Send LDAP Configuration? </h3>
           <form name="f" method="post" action="" id="mo_ldap_export_pop_up">
               <input type="hidden" name="option" value="mo_ldap_login_send_query"/>
               <input type="hidden" id="inner_form_email_id" name="inner_form_email_id" />
               <input type="hidden" id="inner_form_phone_id" name="inner_form_phone_id" />
               <input type="hidden" id="inner_form_query_id" name="inner_form_query_id" />
               <input type="hidden" id="export_configuration_choice" name="export_configuration_choice" value ="yes">
               <div>
                   <p>
                       <h4>Do you also want to send us your configuration information?</h4>
                       <p>It helps us better understand the query and save time.<br>
                       <br>Configuration information includes your :
                       <br>1. LDAP Directory Server
                       <br>2. LDAP Server URL
                       <br>3. Username
                       <br>4. Search Base
                       <br>5. Username Attribute<br>
                       <br>NOTE: <b>No Passwords</b> (Service Account Password) are <b>shared</b> while sending Configuration.
                       </p>
                       <br><br>
                   <div class="mo_ldap_modal-footer" style="text-align: center">
                       <input type="submit" name="miniorange_ldap_export_submit" id="miniorange_ldap_export_submit"
                              class="button button-primary button-large" value="Yes"/ >
                       <input type="button" name="miniorange_ldap_export_skip"
                              class="button button-large" value="No"
                              onclick="document.getElementById('export_configuration_choice').value='no';document.getElementById('mo_ldap_export_pop_up').submit();"/>
                   </div>
               </div>
           </form>
       </div>
   </div>
<?php
}

function feature_request(){

    $current_user = wp_get_current_user();
    if(get_option('mo_ldap_local_admin_email'))
        $admin_email = get_option('mo_ldap_local_admin_email');
    else
        $admin_email = $current_user->user_email;
    ?>
    <div class="mo_ldap_support_layout" id="mo_ldap_support_layout_ldap" style="position: relative; line-height: 2">
        <section class="contact-form">    
            <div class="row">
                <h2 class="mo-ldap-h2">We are happy to hear from you</h2>
                <p class="feature-request-text">
                    Looking for some other features? Reach out to us with your requirements and we will get back to you at the earliest.
                </p>                    
            </div>
            <div class="row">
                <form name="feature_request_form" id="feature_request_form" method="post" action="">
                    <div class="row">
                        <div class="col span-1-of-3 fr-labels">
                            <label for="query_email">Email:</label>
                        </div>
                        <div class="col span-2-of-3 fr-text-boxes">
                            <input type="email" class="mo_ldap_table_textbox" id="query_email" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter your email" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col span-1-of-3 fr-labels">
                            <label for="query_phone">Phone:</label>
                        </div>
                        <div class="col span-2-of-3 fr-text-boxes">
                            <input type="text" class="mo_ldap_table_textbox" name="query_phone" id="query_phone" value="<?php echo get_option('mo_ldap_local_admin_phone'); ?>" placeholder="Enter your phone"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col span-1-of-3 fr-labels">
                            <label for="query">Query:</label>
                        </div>
                        <div class="col span-2-of-3 fr-text-boxes">
                            <textarea id="query" name="query" class="mo_ldap_settings_textarea" style="border-radius:4px;resize: vertical;width:100%" cols="52" rows="7"  placeholder="Write your custom requirement here" required></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col span-1-of-3"></div>
                        <div class="col span-2-of-3">
                            <input type="hidden" name="option" value="mo_ldap_login_send_feature_request_query"/>
                            <input type="button" onclick="sendFeatureRequest()" name="send_query" id="send_query" value="Request Feature" style="margin-bottom:3%;" class="button button-primary button-large" />
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <script>
        jQuery("#query_phone").intlTelInput();


        function sendFeatureRequest() {
            var queryValue = document.getElementById("query").value;

            if (validateEmail()) {
                if (queryValue == "") {
                    alert("Please enter your requirement.");
                } 
                else {
                    jQuery("#feature_request_form").submit();
                }
            }
        }
        function validateEmail()
        {
            var email = document.getElementById('query_email');
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email.value))
            {
                return (true)
            }
            else if(email.value.length == 0){
                alert("Please enter your email address!")
                return (false)
            }
            else
            {
                alert("You have entered an invalid email address!")
                return (false)
            }

        }
    </script>
    <?php
}

function showAddonsList(){

    if( isset( $_GET[ 'tab' ] ) ) {
        $active_tab = $_GET[ 'tab' ];
    }else{
        $active_tab="default";
    }

	    $empty_pointers = get_option("mo_ldap_local_empty_pointers");

        $addonArray = new MoAddonListContent();
        $addonArray = unserialize(MO_LDAP_ADDONS_CONTENT);

        echo '<ul style="list-style-type: none;margin: 0;padding: 0;">';
        $addonNumber = 0;
    foreach ($addonArray as $addonlist) {
        $addonNumber++;
        if( is_plugin_active("buddypress/bp-loader.php") && $addonlist['addonName'] == "Sync BuddyPress Extended Profiles") {
            echo '<li class="activeddon ">
                   <div style="margin-left: 10px;" class="wrapper">
                  <p style="font-size: medium; font-weight: bold;">'.$addonNumber.'. '.$addonlist['addonName'].'</p>
                        <p>'.$addonlist['addonDescription'].'</p>';

            
        } else {
            echo '<li> <div style="margin-left: 10px;">   <p style="font-size: medium; font-weight: bold;">' . $addonNumber . '. ' . $addonlist['addonName'] . '</p>
                        <p>' . $addonlist['addonDescription'] . '</p>
                </div>
          ';
        }

            echo '
<table align="center"><tr>
                    <div>';
        if($addonlist['addonVideo'] != "") {
                       echo '<td style = "padding:10px;" >
<div class="individual-addons-popup-container">
                        <div style = "margin-right: 3%;" id="Add_On_Name" title="'.(string)($addonlist["addonName"]).'">
                            <a onclick="showAddonPopup_video(jQuery(this),title)" style = "display: inline-flex;width: max-content;cursor:pointer;" class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" title = '.$addonlist["addonVideo"].' id = "VideoIcon"><span class="link-text" >Setup Video </span ></a >
                        </div ></div>
                        </td >';
            }
        if($addonlist['addonGuide'] != "") {
            echo '<td style = "padding:10px;" >
                            <div style = "margin-right: 3%;" >
                            <a style = "display: inline-flex;width: max-content;" class="dashicons mo-video-links dashicons-book-alt mo_book_icon" href = '.$addonlist["addonGuide"].' title = "Setup Guide" id = "guideLink" target = "_blank" ><span class="link-text" >Setup Guide </span ></a >
                            </div ></td >';
             }
            echo '</tr></div>
                     </table>
                     </li>';

    }
    echo '</ul>

<div  hidden id="addonVideoModal_PopUp" name="addonVideoModal_PopUp" class="mo_ldap_modal" style="margin-left: 26%">
    <div class="moldap-modal-contatiner-contact-us" syle="color:black"></div>
    
        <!-- Modal content -->
        <div class="mo_ldap_modal-content" id="addonVideo_PopUp" style="width: 650px; padding:10px;">
            <center><span id="add_title_popup" style="font-size: 22px; font-weight: bold;"></span></center>
                <div>
                  <center><iframe width="560" id="iframeVideo_PopUp" height="315" src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></center><br>
                </div>
                  <center><input type="button" style="font-size: medium" name="close_addon_video_modal_PopUp" id="close_addon_video_modal_PopUp" class="button button-primary button-small" value="Close Video" /></center>
        </div>
    </div>
<script>
function showAddonPopup_video(elem,addonSrc){
    setTimeout(function(){
        console.log();
        addonTitle = elem.parent().attr("title");
        jQuery("#iframeVideo_PopUp").attr("src", addonSrc);
        jQuery("span#add_title_popup").text(addonTitle + " Add-on");
    },200);     
      jQuery("#addonVideoModal_PopUp").show();
      jQuery("#wp-pointer-5").css("z-index","0");
    }
   jQuery("#close_addon_video_modal_PopUp").click(function(){
      jQuery("#addonVideoModal_PopUp").hide();
      jQuery("#iframeVideo_PopUp").attr("src", "");
   });

  </script>';
}


function add_on_main_page()
{
    ?>
        <div id="mo_ldap_add_on_layout" class="mo_ldap_support_layout" style="position: relative; margin-top: 20px; line-height: 2;">
            <h2 style="font-size: 20px;">Available Add-ons</h2>
            <?php showAddonsList(); ?>
        </div>
    <?php
}

?>