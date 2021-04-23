<?php
class MoAddonListContent
{

    function __construct()
    {

        define("MO_LDAP_ADDONS_CONTENT",serialize( array(

            "DIRECTORY_SYNC" =>      [
                'addonName'  => 'Sync Users LDAP Directory',
                'addonDescription'  => 'Synchronize Wordpress users with LDAP directory and vice versa. Schedules can be configured for the synchronization to run at a specific time and after a specific interval.',
                'addonPrice' => '169',
                'addonLicense' => 'wp_directory_sync_plan',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-configure-miniorange-directory-sync-add-on-for-wordpress',
                'addonVideo' =>'https://www.youtube.com/embed/DqRtOauJjY8',

            ],
            "KERBEROS_NTLM" =>      [
                'addonName'  => 'Auto Login (SSO) using Kerberos/NTLM',
                'addonDescription'  => 'Provides the feature of auto-login (SSO) into your wordpress site on domain joined machines.',
                'addonPrice' => '169',
                'addonLicense' => 'wp_ntlm_sso_plan',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-kerberos-single-sign-sso',
                'addonVideo' =>'https://www.youtube.com/embed/JCVWurFle9I',

            ],
            "BUDDYPRESS_PROFILE_SYNC" =>      [
                'addonName'  => 'Sync BuddyPress Extended Profiles',
                'addonDescription'  => 'Integration with BuddyPress to sync extended profile of users with LDAP attributes upon login.',
                'addonPrice' => '149',
                'addonLicense' => 'wp_ldap_intranet_buddypress_extended_profiles_plan',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-miniorange-ldap-buddypress-integration-add-on',
                'addonVideo' =>'https://www.youtube.com/embed/7itUoIINyTw',
            ],
            "PASSWORD_SYNC" =>      [
                'addonName'  => 'Password Sync with LDAP Server',
                'addonDescription'  => 'Synchronize your Wordpress profile password with your LDAP user profile.',
                'addonPrice' => '99',
                'addonLicense' => 'ContactUs',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-password-sync-with-ldap-add-on',
                'addonVideo' =>'https://www.youtube.com/embed/6XGUvlvjeUQ',
            ],
            "PROFILE_PICTURE_SYNC" =>      [
                'addonName'  => 'Profile Picture Sync for WordPress and BuddyPress',
                'addonDescription'  => 'Update your WordPress and Buddypress profile picture with thumbnail photos stored in your LDAP directory.',
                'addonPrice' => '119',
                'addonLicense' => 'ContactUs',
                'addonGuide' => 'https://plugins.miniorange.com/configure-miniorange-profile-picture-map-add-on-for-wordpress',
                'addonVideo' =>'https://www.youtube.com/embed/RL_TJ48kV5w',
            ],
            "ULTIMATE_MEMBER_LOGIN_INTEGRATION" =>      [
                'addonName'  => 'Ultimate Member Login Integration',
                'addonDescription'  => 'Login to Ultimate Member with LDAP Credentials.',
                'addonPrice' => '99',
                'addonLicense' => 'ContactUs',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-ultimate-member-login-integration-with-ldap-credentials',
                'addonVideo' =>'https://www.youtube.com/embed/-d2B_0rDFi0',
            ],
            "LDAP_SEARCH_WIDGET" =>      [
                'addonName'  => 'Search Staff from LDAP Directory',
                'addonDescription'  => 'You can search/display your directory users on your website using search widget and shortcode.',
                'addonPrice' => '129',
                'addonLicense' => 'wp_ldap_search_widget_plan',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-miniorange-ldap-search-widget-add-on',
                'addonVideo' =>'https://www.youtube.com/embed/GEw6dOx7hRo',
            ],
            "USER_META" =>      [
                'addonName'  => 'Third Party Plugin User Profile Integration',
                'addonDescription'  => 'Update profile information of any third-party plugin with information from LDAP Directory.',
                'addonPrice' => '149',
                'addonLicense' => 'ContactUs',
                'addonGuide' => 'https://plugins.miniorange.com/guide-to-setup-third-party-user-profile-integration-with-ldap-add-on',
                'addonVideo' =>'https://www.youtube.com/embed/KLKKe4tEiWI',
            ],
            "PAGE_POST_RESTRICTION" =>      [
                'addonName'  => 'Page/Post Restriction',
                'addonDescription'  => 'Allows you to control access to your site\'s content (pages/posts) based on LDAP groups/WordPress roles.',
                'addonPrice' => '149',
                'addonLicense' => 'ContactUs',
                'addonGuide' => 'https://plugins.miniorange.com/wordpress-page-restriction',
                'addonVideo' =>'',
            ],
            "GRAVITY_FORMS_INTEGRATION" =>      [
                'addonName'  => 'Gravity Forms Integration',
                'addonDescription'  => 'Populate Gravity Form fields with information from LDAP. You can integrate with unlimited forms.',
                'addonPrice' => '129',
                'addonLicense' => 'ContactUs',
                'addonGuide' => '',
                'addonVideo' =>'',
            ],
            "BUDDYPRESS_GROUP_SYNC" =>      [
                'addonName'  => 'Sync BuddyPress Groups',
                'addonDescription'  => 'Assign BuddyPress groups to users based on group membership in LDAP.',
                'addonPrice' => '129',
                'addonLicense' => 'ContactUs',
                'addonGuide' => '',
                'addonVideo' =>'',
            ],
            "MEMBERPRESS_INTEGRATION" =>      [
                'addonName'  => 'MemberPress Plugin Integration',
                'addonDescription'  => 'Login to MemberPress protected content with LDAP Credentials.',
                'addonPrice' => '99',
                'addonLicense' => 'ContactUs',
                'addonGuide' => '',
                'addonVideo' =>'',
            ],
            "EMEMBER_INTEGRATION" =>      [
                'addonName'  => 'eMember Plugin Integration',
                'addonDescription'  => 'Login to eMember profiles with LDAP Credentials.',
                'addonPrice' => '99',
                'addonLicense' => 'ContactUs',
                'addonGuide' => '',
                'addonVideo' =>'',
            ],

        )));


    }
    public static function showAddonsContent(){
        $displayMessage = "";
        $messages = unserialize(MO_LDAP_ADDONS_CONTENT);
        echo '<div id="ldap_addon_container" class="mo_ldap_wrapper">';
        $queryBody = "Hi! I am interested in the {{addonName}} addon, could you please tell me more about this addon?";
        foreach ($messages as $messageKey)
        {
            if($messageKey['addonName'] != 'Auto Login (SSO) using Kerberos/NTLM') {
                echo'
                    <div class="cd-pricing-wrapper-addons">
                        <div data-type="singlesite" class="is-visible ldap-addon-box">
                        <div class="individual-container-addons" style="height:100%;" >
                            <header class="cd-pricing-header">
                               <div style="height:35px"> <h2 id="addonNameh2" title='.$messageKey['addonVideo'].'>'.$messageKey['addonName'].'</h2>
                               </div><br>
                                <hr >';

                echo '<center> <div style="margin-right: 3%;">';
                if(!empty($messageKey['addonVideo'])) {echo'
                            <a onclick="showAddonPopup(jQuery(this))" class="dashicons mo-form-links dashicons-video-alt3 mo_video_icon" id="addonVideos" href="#addonVideos" style="width:max-content;"><span class="link-text" style="color: black;">Setup Video</span></a>
                             ';}
                if(!empty($messageKey['addonGuide'])) {echo'
                            <a class="dashicons mo-form-links dashicons-book-alt mo_book_icon" href='.$messageKey['addonGuide'].' title="Setup Guide" id="guideLink"  target="_blank" style="width:max-content;"><span class="link-text" style="color: black;">Setup Guide</span></a>
                            ';}echo'</div></center>';


               if(empty($messageKey['addonVideo']) || empty($messageKey['addonGuide'])) {
                echo '<div style="margin-right: 4%;height: 20px"></div>';
            }
            echo'
                                <div style="height: 100px;display: grid;align-items: center;"><h3  class="subtitle" style="color:black;padding-left:unset;vertical-align: middle;text-align: center;letter-spacing: 1px">'.$messageKey['addonDescription'].'</h3></div><br>
                                <div class="cd-priceAddon">
                                    <span class="cd-currency">$</span>
                                    <!-- <span class="cd-value">149*</span></span> -->
                                    <div style="display:inline"><span class="cd-value" id="addon2Price" >'.$messageKey['addonPrice'].' </span><p style="display:inline;font-size:20px" id="addon2Text"> / instance</p></span>
                                 </div>

                                </div>
                            </header> <!-- .cd-pricing-header -->
                            <footer>
                                <a id="" href="#" style="text-align: center;display:inherit" class="cd-select" ';
                                

                                if($messageKey['addonLicense'] !== "ContactUs") {
                                    $linkText = "Upgrade Now";
                                    $onclick = 'onclick="upgradeform(\''.$messageKey['addonLicense'].'\')"';
                } else {
                                    $linkText = "Contact Us";
                                    $onclick = 'onclick="openSupportForm(\''.$messageKey['addonName'].'\')"';
                                }


            echo $onclick. ' >' .$linkText.' </a>
                             </footer>
                        </div>
                      
                       
                    </div> </div>
                ';

        }
        }
        echo '</div><br>
 <div  hidden id="addonVideoModal" name="addonVideoModal" class="mo_ldap_modal" style="margin-left: 26%">
    <div class="moldap-modal-contatiner-contact-us" syle="color:black"></div>
        <!-- Modal content -->
        <div class="mo_ldap_modal-content" id="addonVideoPopUp" style="width: 650px; padding:10px;"><br>
            <center><span id="add_title" style="font-size: 22px; margin-left: 50px; font-weight: bold;"></span></center><br>
                <div>
                  <center><iframe width="560" id="iframeVideo" height="315" src="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></center><br>
                </div>
                  <center><input type="button" style="font-size: medium" name="close_addon_video_modal" id="close_addon_video_modal" class="button button-primary button-small" value="Close Video" /></center>
        </div>

    </div>
<script>
function showAddonPopup(elem){
    setTimeout(function(){
        addonTitle = elem.parents(".individual-container-addons.activeCurrent").find("#addonNameh2").text();
        addonSrc = elem.parents(".individual-container-addons.activeCurrent").find("#addonNameh2").attr("title");
        jQuery("#iframeVideo").attr("src", addonSrc);
        jQuery("span#add_title").text(addonTitle + " Add-on");
    },200);     
      jQuery("#addonVideoModal").show();
    }
   jQuery("#close_addon_video_modal").click(function(){
      jQuery("#addonVideoModal").hide();
      jQuery("#iframeVideo").attr("src", "");
   });

</script>';
        return $displayMessage;
    }
}