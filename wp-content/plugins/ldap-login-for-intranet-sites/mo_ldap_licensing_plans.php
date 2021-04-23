<?php
include_once dirname( __FILE__ ) . '/add-on-framework.php';
include_once dirname( __FILE__ ) . '/ldap_pricing.php';

function mo_ldap_show_licensing_page(){
    echo '<style>.update-nag, .updated, .error, .is-dismissible, .notice, .notice-error { display: none; }</style>';
    wp_enqueue_style('mo_ldap_license_page_style', plugins_url('includes/css/mo_ldap_license_page.min.css', __FILE__));
    wp_enqueue_style('Grid_Layout_License_Page', plugins_url('includes/css/grid.min.css', __FILE__));
?>

<div id="navbar">
  <a href="#licensing_plans" id="plans-section" class="navbar-links">Plans</a>
  <a href="#section-features" id="features-section" class="navbar-links">Feature Comparison</a>
  <a href="#upgrade-steps" id="upgrade-section" class="navbar-links">Upgrade Steps</a>
  <a href="#section-addons" id="addons-section" class="navbar-links">Add - Ons</a>
</div>

<script>

    window.onscroll = function() {ldapStickyNavbar()};

    var navbar = document.getElementById("navbar");
    var sticky = navbar.offsetTop;

    function ldapStickyNavbar() {
        if (window.pageYOffset >= sticky) {
            navbar.classList.add("sticky")
        } else {
            navbar.classList.remove("sticky");
        }
    }

</script>

<div style="text-align: center; font-size: 14px; color: white; padding-top: 4px; padding-bottom: 4px; border-radius: 16px;"></div>
<input type="hidden" id="mo_license_plan_selected" value="licensing_plan" />
<div class="tab-content">
	<div class="tab-pane active text-center" id="cloud">
		<div class="cd-pricing-container cd-has-margins" style="max-width: unset">
			<div class="ldap_center_div" style="text-align: center;background-color:#f9f9f9;padding-top:30px;" onmouseenter="onMouseEnterPlans()" onmouseleave="onMouseLeavePlans()" id="licensing_plans">
				<div class="ldap_heading" style="display: inline-block;">
					<br>
					<?php update_option( 'mo_ldap_license_flag',1); ?>
					<h1 style="font-size: 32px ;">Choose Your Licensing Plan</h1>
					<br>
                </div>
                <div style="display: flex;justify-content: center;position: relative;line-height:28px;">
					<h4 style="font-size: 20px ; color: red;text-align-center;">Are you not able to choose your plan?</h4> 
                    <a class="button button-primary" style="font-size:15px; position: absolute;margin-left:500px;" name="licensingContactUs" href="#licensingContactUs">Contact Us</a>
                </div>
			</div>
			<div class="cd-pricing-switcher" onmouseenter="onMouseEnterPlans()" onmouseleave="onMouseLeavePlans()">
				<p class="fieldset" style="background-color: #e97d68;">
					<input type="radio" name="sitetype" value="singlesite" id="singlesite" checked>
					<label for="singlesite">Single Site</label>
					<input type="radio" name="sitetype" value="multisite" id="multisite">
					<label for="multisite">Multisite Network</label>
				</p>
			</div>

            <script>

                jQuery(document).ready(function () {
                $ldap = jQuery;
                //to change the active class for Addons
                $ldap(".individual-container-addons").click(function () {
                    $ldap('.individual-container-addons').addClass('activeCurrent');
                    $ldap('.individual-container-addons').not(this).removeClass('activeCurrent');
                })
                $ldap("#singlesite_plans > .ldap_li select").click(function (e) {
                    return false;
                });
                $ldap("#multisite_plans > .ldap_li_mul select").click(function (e) {
                    return false;
                });
                //to change the active class for singleSitePlans
                $ldap("#singlesite_plans > .ldap_li").click(function (e) {
                    var selectElemID = $ldap(this).find("li[data-type='singlesite']").attr("id").toLowerCase();
                    if (selectElemID.includes("pricing")) return true;
                    $ldap('.ldap_li').find(".individual-container").addClass('activeCurrent');
                    $ldap('.ldap_li').not(this).find(".individual-container").removeClass('activeCurrent');
                })
                //to change the active class for multiSitePlans
                $ldap("#multisite_plans > .ldap_li_mul").click(function (e) {
                    var selectElemID = $ldap(this).find("li[data-type='multisite']").attr("id").toLowerCase();
                    if (selectElemID.includes("pricing")) return true;
                    $ldap('.ldap_li_mul').find(".individual-container").addClass('activeCurrent');
                    $ldap('.ldap_li_mul').not(this).find(".individual-container").removeClass('activeCurrent');
                })
            });
            var selectArray = JSON.parse('<?php echo json_encode(new MoLicensePlansPricing()) ?>');

            function createSelectOptions(elemId) {
                var selectPricingArray = selectArray[elemId];
                var selectElem = ' <div class="cd-price"><span class="cd-currency">$</span><span class="cd-value" id="standardID">' + selectArray[elemId]["1"] + '</span></span></div>' + '<span style="font-size:15px">(One Time Payment)</span></header> <!-- .cd-pricing-header --></a>' + '<footer class="cd-pricing-footer"><h3 class="instanceClass" >No. of instances:';
                var selectElem = selectElem + ' <select class="selectInstancesClass" required="true" onchange="changePricing(this)" id="' + elemId + '">';
                jQuery.each(selectPricingArray, function (instances, price) {
                    selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '">' + instances + ' </option>';
                })
                selectElem = selectElem + "</select>";
                return document.write(selectElem);
            }

            function createSelectWithSubsitesOptions(elemId) {
                var selectPricingArray = selectArray[elemId];
                var selectSubsitePricingArray = selectArray['subsiteIntances'];
                var selectElem = ' <div class="cd-price"><span class="cd-currency">$</span><span class="cd-value" id="standardID">' + selectArray[elemId]["1"] + '</span></span></div>' + '<span style="font-size:15px">(One Time Payment)</span></header> <!-- .cd-pricing-header --></a>' + '<footer class="cd-pricing-footer"><div style="display: inline-block;float: left;"><h3 class="instanceClass" >No. of instances:';
                var selectElem = selectElem + ' <select class="selectInstancesClass" required="true" onchange="changePricing(this)" id="' + elemId + '">';
                jQuery.each(selectPricingArray, function (instances, price) {
                    selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '">' + instances + ' </option>';
                })
                selectElem = selectElem + "</select></h3>";
                selectElem = selectElem + '<br><h3 class="instanceClass" >No. of subsites:&nbsp&nbsp';
                selectElem = selectElem + '<select class="selectInstancesClass" required="true" onchange="changePricing(this)" id="' + elemId + '" name="' + elemId + '-subsite">';
                jQuery.each(selectSubsitePricingArray, function (instances, price) {
                    selectElem = selectElem + '<option value="' + instances + '" data-value="' + instances + '">' + instances + ' </option>';
                })
                selectElem = selectElem + "</select></h3></div>";
                return document.write(selectElem);
            }

            function changePricing($this) {
                var selectId = jQuery($this).attr("id");
                var selectSubsiteValue = jQuery("select[name=" + selectId + "-subsite]").val();
                var e = document.getElementById(selectId);
                var strUser = e.options[e.selectedIndex].value;
                var strUserInstances = strUser != "UNLIMITED" ? strUser : 500;
                selectArrayElement = [];
                selectSubsiteArrayElement = selectArray.subsiteIntances[selectSubsiteValue];
                if (selectId == "pricingCustomProfile") selectArrayElement = selectArray.pricingCustomProfile[strUser];
                if (selectId == "pricingKerberos") selectArrayElement = selectArray.pricingKerberos[strUser];
                if (selectId == "pricingEnterprise") selectArrayElement = selectArray.pricingEnterprise[strUser];
                if (selectId == "mulPricingCustomProfile") selectArrayElement = parseInt(selectArray.mulPricingCustomProfile[strUser].replace(",", "")) + parseInt(parseInt(selectSubsiteArrayElement) * parseInt(strUserInstances));
                if (selectId == "mulPricingKerberos") selectArrayElement = parseInt(selectArray.mulPricingKerberos[strUser].replace(",", "")) + parseInt(parseInt(selectSubsiteArrayElement) * parseInt(strUserInstances));
                if (selectId == "mulPricingEnterprise") selectArrayElement = parseInt(selectArray.mulPricingEnterprise[strUser].replace(",", "")) + parseInt(parseInt(selectSubsiteArrayElement) * parseInt(strUserInstances));
                jQuery("#" + selectId).parents("div.individual-container").find(".cd-value").text(selectArrayElement);
            }
            </script>
                <div class="section-plans" id="section-plans" onmouseenter="onMouseEnterPlans()" onmouseleave="onMouseLeavePlans()"> 
                    <div class="plan-boxes">
                    <input type="hidden" value="<?php echo Mo_Ldap_Local_Util::is_customer_registered()?>" id="mo_customer_registered">
                        <ul class="cd-pricing-list cd-bounce-invert">
                            <div id="singlesite_plans" style="display: grid;grid-template-columns: repeat(3, 1fr);grid-gap: 30px;">
                                <li class="ldap_li">
                                    <ul class="cd-pricing-wrapper">
                                        <li name="listPlans" data-type="singlesite" id="standard" class="mosslp" style="border: <?php echo $sspborder; ?>">
                                            <div id="0" class="individual-container">
                                                <a id="popover1" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <h2 class="plan_name" style="margin-bottom: 10px">Basic LDAP Authentication Plan<span style="font-si ze:0.5em"></span></h2>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"><a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://www.youtube.com/watch?v=r0pnB2d0QP8" style="width:max-content;"><span class="link-text" style="color: black;">Premium Features</span></a></div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">Sync user profile information and assign wordpress roles based on LDAP groups</h3> </div>
                                                        <script>
                                                        createSelectOptions('pricingCustomProfile');
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_intranet_premium_plan')">Upgrade Now</a>
                                        </footer>
                                    </ul>
                                </li>
                            
                                <li class="ldap_li">
                                    <ul class="cd-pricing-wrapper ">
                                        <li name="listPlans" data-type="singlesite" id="standard" class="mosslp" style="border: <?php echo $sseborder; ?>">
                                            <div id="1" class="individual-container activeCurrent">
                                                <a id="popover2" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <div class="special">
                                                            <div class="popular">Popular</div>
                                                        </div>
                                                        <h2 class="plan_name" style="margin-bottom: 10px">Basic LDAP Authentication Plan + Kerberos/NTLM SSO</h2>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"> <a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://youtu.be/JCVWurFle9I" style="width:max-content;"><span class="link-text" style="color: black;">Auto-login (SSO) Features</span></a> </div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">All features along with auto-login (SSO) into your wordpress site on domain joined machine's</h3> </div>
                                                        <script>
                                                        createSelectOptions('pricingKerberos')
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_ntlm_sso_bundled_plan')">Upgrade Now</a> 
                                        </footer>
                                    </ul>
                                </li>
                                <!-- ######################################## Enterprise/All-inclusive ############################-->
                                <li class="ldap_li">
                                    <ul class="cd-pricing-wrapper">
                                        <li data-type="singlesite" id="standard" class="mosslp" style="border: <?php echo $sseborder; ?>">
                                            <div id="2" class="individual-container ">
                                                <a id="popover3" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <div class="special">
                                                            <div class="popular">Popular</div>
                                                        </div>
                                                        <h2 class="plan_name" style="margin-bottom:10px;">Enterprise/All-Inclusive Plan</h2>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"> <a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://www.youtube.com/playlist?list=PL2vweZ-PcNpd3lEzmiLZwL_cAG_Evg2QC" style="width:max-content;"><span class="link-text" style="color: black;">Premium + All Add-ons Features</span></a> </div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">All features along with access to all premium add-ons<br /><br /></h3> </div>
                                                        <script>
                                                            createSelectOptions('pricingEnterprise')
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_all_inclusive_bundled_plan')">Upgrade Now</a> 
                                        </footer>
                                    </ul>
                                </li>
                            </div>
                            <!--##########################################MULTISITE PLANS######################################  -->
                            <div id="multisite_plans" style=" display:none;grid-template-columns: repeat(3, 1fr);grid-gap: 30px;">
                                <li class="ldap_li_mul">
                                    <ul class="cd-pricing-wrapper">
                                        <li name="listPlans" data-type="multisite" id="multisite" class="momslp" style="border: <?php echo $sspborder; ?>">
                                            <div id="0" class="individual-container">
                                                <a id="popover1" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <!-- <span class="specialTag"><div class="popularTag">Popular</div></span> -->
                                                        <h2 class="plan_name" style="margin-bottom: 10px">Multisite Basic LDAP <br> Authentiction <br> Plan<span style="font-si ze:0.5em"></span></h2>
                                                        <br>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"> <a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://www.youtube.com/watch?v=r0pnB2d0QP8" style="width:max-content;"><span class="link-text" style="color: black;">Premium Features</span></a> </div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">Sync user profile information and assign wordpress roles based on LDAP groups<br /><br /></h3> </div>
                                                        <script>
                                                        createSelectWithSubsitesOptions('mulPricingCustomProfile');
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_intranet_multisite_premium_plan')">Upgrade Now</a> 
                                        </footer>
                                    </ul>
                                </li>

                                <li class="ldap_li_mul">
                                    <ul class="cd-pricing-wrapper ">
                                        <li name="listPlans" data-type="multisite" id="multisite" class="momslp" style="border: <?php echo $sseborder; ?>">
                                            <div id="1" class="individual-container activeCurrent">
                                                <a id="popover2" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <div class="special">
                                                            <div class="popular">Popular</div>
                                                        </div>
                                                        <h2 class="plan_name" style="margin-bottom: 10px">Multisite Basic LDAP Authentication Plan + Kerberos/NTLM SSO</h2>
                                                        <br>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"> <a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://youtu.be/JCVWurFle9I" style="width:max-content;"><span class="link-text" style="color: black;">Auto-login (SSO) Features</span></a> </div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">All features along with auto-login (SSO) into your wordpress site on domain joined machine's<br /><br /></h3> </div>
                                                        <script>
                                                        createSelectWithSubsitesOptions('mulPricingKerberos')
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_ntlm_sso_multisite_bundled_plan')">Upgrade Now</a> 
                                        </footer>
                                    </ul>
                                </li>
                                <!-- ######################################## Enterprise/All-inclusive ############################-->
                                <li class="ldap_li_mul">
                                    <ul class="cd-pricing-wrapper">
                                        <li data-type="multisite" id="multisite" class="momslp" style="border: <?php echo $sseborder; ?>">
                                            <div id="2" class="individual-container ">
                                                <a id="popover3" data-toggle="popover">
                                                    <header class="cd-pricing-header">
                                                        <div class="special">
                                                            <div class="popular">Popular</div>
                                                        </div>
                                                        <h2 class="plan_name" style="margin-bottom:10px;">Multisite Enterprise/All-Inclusive Plan</h2>
                                                        <br>
                                                        <br>
                                                        <hr>
                                                        <center>
                                                            <div style="margin-right: 3%;"> <a class="dashicons mo-video-links dashicons-video-alt3 mo_video_icon" target="_blank" href="https://www.youtube.com/playlist?list=PL2vweZ-PcNpd3lEzmiLZwL_cAG_Evg2QC" style="width:max-content;"><span class="link-text" style="color: black;">Premium + All Add-ons Features</span></a> </div>
                                                        </center>
                                                        <br>
                                                        <div class="subheading">
                                                            <h3 class="subheading_plan">All features along with access to all premium add-ons<br /><br /></h3> </div>
                                                        <script>
                                                            createSelectWithSubsitesOptions('mulPricingEnterprise')
                                                        </script>
                                                    </header>
                                                </a>
                                            </div>
                                        </li>
                                        <footer class="cd-pricing-footer"> 
                                            <a href="#" class="cd-select cd-select-plans" onclick="upgradeform('wp_ldap_all_inclusive_multisite_bundled_plan')">Upgrade Now</a> 
                                        </footer>
                                    </ul>
                                </li>
                            </div>
                        </ul>
                    </div>
                </div>

                <div class="PricingCard-toggle ldap-plan-title feature-section-heading" id="section-features" onmouseenter="onMouseEnterFeatures()" onmouseleave="onMouseLeaveFeatures()">
                    <h2 class="mo-ldap-h2"> Features Comparison</h2>
                </div>
            <div class="section-features" onmouseenter="onMouseEnterFeatures()" onmouseleave="onMouseLeaveFeatures()">
                <div class="collapse" id="collapseExample" style="width:90%;">
                    <table class="FeatureList">
                        <tr id="feature_list">
                            <th style="color: white;"> Features List </th>
                            <th style="color: white;"> Basic LDAP Authentication Plan </th>
                            <th style="color: white;"> Basic LDAP Authentication Plan + Kerberos/NTLM SSO </th>
                            <th style="color: white;"> Enterprise/All-Inclusive Plan </th>
                        </tr>
                        <tr>
                            <td class="features">Custom WordPress Profile Mapping</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Assign WordPress roles based on LDAP groups</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Support for fetching LDAP groups automatically for role mapping</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Authenticate users from Multiple LDAP Search Bases</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Support for automatic selection of LDAP OU's as a search base</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Automatic Custom Search filter builder with group restriction</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Authenticate users from both LDAP and WordPress</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">WordPress to LDAP user profile update</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Auto-register of LDAP users in WordPress site</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Redirect to custom URL after authentication</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Support for LDAPS for Secure Connection to LDAP Server</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Detailed user authentication report</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Support for Import/Export plugin configuration</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Auto-login (SSO) into WordPress site with Kerberos/NTLM</td>
                            <td class="features"><i class="fa fa-times" aria-hidden="true" style="color: red"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                        <tr>
                            <td class="features">Access to all premium add-ons</td>
                            <td class="features"><i style="color: red"></i>(Separate Purchase)</td>
                            <td class="features"><i style="color: red"></i>(Separate Purchase)</td>
                            <td class="features"><i class="fa fa-check"></i></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="PricingCard-toggle ldap-plan-title mul-dir-heading">
                <h2 class="mo-ldap-h2">Multiple Directories Plan</h2>
            </div>
            <div class="multiple-dir-text">
                <h2 style="padding:0 20px;width:30%;text-align:left;">Looking for LDAP Authentication against more than one LDAP Server?</h2>
                <p style="margin-left:80px;font-size:18px;color:green; font-style: italic;font-weight:600;width:40%;"> We do support LDAP authentication from multiple LDAP directories in our Multiple LDAP Directories Plan. To get more details on this plan please <a name="MultipleDirContactUs" href="#MultipleDirContactUs">contact us</a>.</p>
            </div>
            
                    <div class="PricingCard-toggle ldap-plan-title mul-dir-heading" id="upgrade-steps" onmouseenter="onMouseEnterUpgrade()" onmouseleave="onMouseLeaveUpgrade()">
                        <h2 class="mo-ldap-h2">How to upgrade to premium</h2>
                    </div>
                    <section class="section-steps"  id="section-steps" onmouseenter="onMouseEnterUpgrade()" onmouseleave="onMouseLeaveUpgrade()">
                    <div class="row">
                            <div class="col span-1-of-2 steps-box">
                                <div class="works-step">
                                    <div>1</div>
                                    <p>
                                        Click on Upgrade Now button for required premium plan and you will be redirected to miniOrange login console.
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>2</div>
                                    <p>
                                        Enter your username and password with which you have created an account with us. After that you will be redirected to payment page.
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>3</div>
                                    <p>
                                        Enter your card details and proceed for payment. On successful payment completion, the premium plugin(s) and add-on(s) will be available to download.
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>4</div>
                                    <p>
                                        Download the premium plugin(s) and add-on(s) from Plugin Releases and Downloads section.
                                    </p>
                                </div>
                            </div>
                            <div class="col span-1-of-2 steps-box">
                                <div class="works-step">
                                    <div>5</div>
                                    <p>
                                        From the WordPress admin dashboard, delete the free plugin currently installed.
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>6</div>
                                    <p style="padding-top:10px;">
                                        Unzip the downloaded premium plugin and extract the files. <br> <br>
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>7</div>
                                    <p>
                                        Upload the extracted files using FTP to path /wp-content/plugins/. Alternately, go to Add New → Upload Plugin in the plugin's section to install the .zip file directly.<br>
                                    </p>
                                </div>
                                <div class="works-step">
                                    <div>8</div>
                                    <p>
                                        After activating the premium plugin, login using the account you have registered with us.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="font-size:16px;padding-bottom:25px;">
                           <b>Note: </b>The premium plans are available in the miniOrange dashboard. Please don't update the premium plugin from the WordPress Marketplace. We'll notify you via email whenever a newer version of the plugin is available in the miniOrange dashboard.
                        </div>    
                    </section>

            <div class="PricingCard-toggle ldap-plan-title yt-video-heading">
                <h2 class="mo-ldap-h2"> Watch Premium Version Features</h2>
            </div>
            <div class="section-license-page-video">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/r0pnB2d0QP8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>   
            </div>

            <div class="PricingCard-toggle ldap-plan-title inst-subs-heading">
                <h2 class="mo-ldap-h2"> Instance - Subsites Definition</h2>
            </div>

            <div class="instance-subsites">
                <div class="row">
                    <div class="col span-1-of-2 instance-box">
                        <h3 class="myH3">What is an instance?</h3><br>
                        <br>A WordPress instance refers to a single installation of a WordPress site. It refers to each individual website where the plugin is active. In the case of a single site WordPress, each website will be counted as a single instance.
                        <br>
                        <br> For example, You have 3 sites hosted like one each for development, staging, and production. This will be counted as 3 instances.
                    </div>
                    <div class="col span-1-of-2 subsite-box">
                        <h3 class="myH4">What is a multisite network?</h3><br>
                        <br>A multisite network means managing multiple sites within the same WordPress installation and has the same database.
                        <br>
                        <br>For example, You have 1 WordPress instance/site with 3 subsites in it then it will be counted as 1 instance with 3 subsites.
                        <br> You have 1 WordPress instance/site with 3 subsites and another WordPress instance/site with 2 subsites then it will be counted as 2 instances with 3 subsites.
                    </div>
                </div>
            </div>
            
            <div class="PricingCard-toggle ldap-plan-title" style="padding: unset; height: 50px;">
                <h2 class="PricingCard-toggle" style="color:black; padding-top: 15px;">Integrate the popular plugins with miniOrange LDAP/AD plugin</h2> </h2>
            </div>            
            <div class="section-integration">
                <div class="row int-img-row">
                    <div class="col span-1-of-4">
                        <img class="integration-images" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/images/woocommerce.png'; ?>" alt="">
                    </div>
                    <div class="col span-1-of-4">
                        <img class="integration-images" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/images/gravity-forms.png'; ?>" alt="">
                    </div>
                    <div class="col span-1-of-4">
                        <img class="integration-images" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/images/ultimate-member.png'; ?>" alt="">
                    </div>
                    <div class="col span-1-of-4">
                        <img class="integration-images" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/images/buddypress.png'; ?>" alt="">
                    </div>
                </div>
            </div>

            <script> 

                jQuery('a[name=MultipleDirContactUs]').click(
                    function(){
                        jQuery('#licensingContactUsModal').show();
                        jQuery("#contact_us_title").text("Contact Us for LDAP Multiple Directories Premium Plan");
                        query = "Hi!! I am interested in LDAP Multiple Directories Premium Plan and want to know more about it.";
                        jQuery("#mo_ldap_licensing_contact_us #query").val(query);
                    });

                jQuery('#multiple_ldap_directories_contact_us_close').click(
                    function(){
                        jQuery("#mo_ldap_licensing_contact_us #query").val('');
                        jQuery('#licensingContactUsModal').hide();
                    });
            </script>
                <br>
                <div class="cd-pricing-container cd-has-margins" style="max-width: unset">
                    <div class="section-addons" id="section-addons" onmouseenter="onMouseEnterAddons()" onmouseleave="onMouseLeaveAddons()">
                        <h2 class="mo-ldap-h2">Premium Add-ons </h2>
                        <div> 
                            <p style="font-size:16px;font-weight:500;margin-bottom:30px;text-align:center;color:#000;"> (Requires Basic LDAP Authentication Plan) </p> 
                        </div>
                        <div class="premium-addons" onmouseenter="onMouseEnterAddons()" onmouseleave="onMouseLeaveAddons()">
                            <input type="hidden" value="<?php echo Mo_Ldap_Local_Util::is_customer_registered();?>" id="mo_customer_registered">
                            <?php
                                $adddonObj = new MoAddonListContent();
                                $adddonObj->showAddonsContent();
                            ?>
                        </div>
                    </div>
                    
                    <section class="payment-methods">
                        <div class="row">
                            <h2 class="mo-ldap-h2">Supported Payment Methods</h2>
                        </div>
                        <div class="row">
                            <div class="col span-1-of-3">
                                <div class="plan-box">
                                    <div>
                                        <i style="font-size:30px;" class="fa fa-cc-amex" aria-hidden="true"></i>
                                        <i style="font-size:30px;" class="fa fa-cc-visa" aria-hidden="true"></i>
                                        <i style="font-size:30px;" class="fa fa-cc-mastercard" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        If the payment is made through Credit Card/International Debit Card, the license will be created automatically once the payment is completed.
                                    </div>
                                </div>
                            </div>
                            <div class="col span-1-of-3">
                                <div class="plan-box">
                                    <div>
                                        <img class="payment-images" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/css/images/paypal.png'; ?>" alt="">
                                    </div>
                                    <div>
                                        Use the following PayPal ID for making the payment via PayPal.<br><br> <span style="color:blue;text-decoration:underline;">info@xecurify.com</span> 
                                    </div>
                                </div>
                            </div>
                            <div class="col span-1-of-3">
                                <div class="plan-box">
                                    <div>
                                        <!-- <img class="payment-images card-image" src="<?php echo plugin_dir_url( __FILE__ ) . 'includes/css/images/banktransfer.png'; ?>" alt=""> -->
                                        <i style="font-size:30px;" class="fa fa-university" aria-hidden="true"><span style="font-size: 20px;font-weight:500;">&nbsp;&nbsp;Bank Transfer</span></i>
                                         
                                    </div>
                                    <div>
                                        If you want to use bank transfer for the payment then contact us at <span style="color:blue;text-decoration:underline;">info@xecurify.com</span>  so that we can provide you the bank details.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <p style="margin-top:20px;font-size:16px;">
                                <span style="font-weight:500;"> Note :</span> Once you have paid through PayPal/Net Banking, please inform us so that we can confirm and update your license.
                            </p>
                        </div>
                    </section>

                    <div class="PricingCard-toggle ldap-plan-title mul-dir-heading">
                        <h2 class="mo-ldap-h2">10 days Return Policy</h2>
                    </div>
                    <section class="return-policy">
                        <p style="font-size:16px;">
                            If the premium plugin you purchased is not working as advertised and you’ve attempted to resolve any feature issues with our support team, which couldn't get resolved, we will refund the whole amount within 10 days of the purchase. <br><br>
                            <span style="color:red;font-weight:500;font-size:18px;">Note that this policy does not cover the following cases: </span> <br><br>
                            <span> 1. Change of mind or change in requirements after purchase. <br>
                                   2. Infrastructure issues not allowing the functionality to work.
                            </span> <br><br>
                            Please email us at <a href="mailto:info@xecurify.com">info@xecurify.com</a> for any queries regarding the return policy.
                            <a href="#nav-container" class="button button-primary button-large back-to-top" style="font-size:15px;">Top &nbsp;↑</a>
                        </p>
                        
                    </section>
                <!-- Modal -->
                <?php
                    $current_user = wp_get_current_user();
                    if(get_option('mo_ldap_local_admin_email'))
                        $admin_email = get_option('mo_ldap_local_admin_email');
                    else
                    $admin_email = $current_user->user_email; 
                ?>
                <div hidden id="licensingContactUsModal" name="licensingContactUsModal" class="mo_ldap_modal" style="margin-left: 26%;z-index:11;">
                    <div class="moldap-modal-contatiner-contact-us" syle="color:black;"></div>
                    <!-- Modal content -->
                    <div class="mo_ldap_modal-content" id="contactUsPopUp" style="width: 700px; padding:30px;"> <span id="contact_us_title" style="font-size: 22px; margin-left: 50px; font-weight: bold;">Contact Us for Choosing the Correct Premium Plan</span>
                        <form name="f" method="post" action="" id="mo_ldap_licensing_contact_us" style="font-size: large;">
                            <input type="hidden" name="option" value="mo_ldap_login_send_feature_request_query" />
                            <div>
                                <p style="font-size: large;">
                                    <br> <b>Email: </b>
                                    <input style=" width: 77%; margin-left: 69px; " type="email" class="mo_ldap_table_textbox" id="query_email" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter email address through which we can reach out to you" required />
                                    <br>
                                    <br> <b style="display:inline-block; vertical-align: top;">Description: </b>
                                    <textarea style="width:77%; margin-left: 21px;" id="query" name="query" required rows="5" style="width: 100%" placeholder="Tell us which features you require"></textarea>
                                </p>
                                <br>
                                <br>
                                <div class="mo_ldap_modal-footer" style="text-align: center">
                                    <input type="button" style="font-size: medium" name="miniorange_ldap_feedback_submit" id="miniorange_ldap_feedback_submit" class="button button-primary button-small" onclick="validateRequirement()" value="Submit" />
                                    <input type="button" style="font-size: medium" name="miniorange_ldap_licensing_contact_us_close" id="miniorange_ldap_licensing_contact_us_close" class="button button-primary button-small" value="Close" /> 
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <form style="display:none;" id="loginform" action="<?php echo get_option( 'mo_ldap_local_host_name' ). '/moas/login'; ?>" target="_blank" method="post">
                    <input type="email" name="username" value="<?php echo get_option( 'mo_ldap_admin_email' ); ?>" />
                    <input type="text" name="redirectUrl" value="<?php echo get_option( 'mo_ldap_local_host_name' ). '/moas/initializepayment'; ?>" />
                    <input type="text" name="requestOrigin" id="requestOrigin" /> 
                </form> 
                <a id="mo_backto_ldap_accountsetup_tab" style="display:none;" href="<?php echo add_query_arg( array( 'tab' => 'account' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>">Back</a>

                <script >
                    jQuery('.popupCloseButton').click(function () {
                        jQuery('.popup').hide();
                    });
                    jQuery('.popup').click(function () {
                        jQuery('.popup').hide();
                    });
                    jQuery('a[name=licensingContactUs]').click(function () {
                        // jQuery("#contactUsPopUp").show();
                        jQuery('#licensingContactUsModal').show();
                        jQuery("#contact_us_title").text("Contact Us for Choosing the Correct Premium Plan");
                    });
                    jQuery('#miniorange_ldap_licensing_contact_us_close').click(function () {
                        // jQuery("#contactUsPopUp").hide();
                        jQuery("#mo_ldap_licensing_contact_us #query").val('');
                        jQuery('#licensingContactUsModal').hide();
                    });
                    jQuery(document).ready(function ($) {
                        $('#buttonToggleCollapseAddon').click(function () {
                            $('#buttonToggleAddon').show();
                        });
                        $('#buttonToggleThirdPartyAddon').click(function () {
                            $('#buttonToggleThirdPartyAddon').hide();
                        });
                        $('#buttonToggleCollapseThirdParyAddon').click(function () {
                            $('#buttonToggleThirdPartyAddon').show();
                        });
                        $('#sso-mfa-features').click(function () {
                            if ($('#show-sso-mfa-features').hasClass('in')) {
                                $('#sso-mfa-features-icon').removeClass('arrow-rotate-180').addClass('arrow-rotate-zero');
                                $('#sso-mfa-features').text('Show Features');
                            } else {
                                $('#sso-mfa-features-icon').removeClass('arrow-rotate-zero').addClass('arrow-rotate-180');
                                $('#sso-mfa-features').text('Collapse Features');
                            }
                        });
                    });

                    function hideElements() {
                        jQuery(document).ready(function ($) {
                            var x = document.getElementById("myDIV");
                            var toggle_button = document.getElementById("toggleBack");
                            if (x.style.display === "block") {
                                x.style.display = "none";
                                toggle_button.style.display = "none";
                                $('#toggleBack').removeClass('PricingCard-toggle');
                                $('#toggleBack').addClass('PricingCard-toggleBack');
                            }
                        });
                    }
                    setTimeout(function () {
                        var elmnt = document.getElementById("success");
                        var elmnt1 = document.getElementById("error");
                        if (elmnt1) {
                            jQuery(elmnt1).css("display", "block");
                            jQuery(elmnt1).css("margin-top", "1%");
                        } else if (elmnt) {
                            jQuery(elmnt).css("display", "block");
                            jQuery(elmnt).css("margin-top", "1%");
                        }
                        document.body.scrollTop = 0;
                        document.documentElement.scrollTop = 0;
                    }, 60);

                    function validateRequirement() {
                        if (validateEmail()) {
                            var requirement = document.getElementById("query").value;
                            if (requirement.length <= 10) {
                                alert("Please enter more details about your requirement.");
                            } else {
                                document.getElementById("mo_ldap_licensing_contact_us").submit();
                            }
                        }
                    }

                    function validateEmail() {
                        var email = document.getElementById('query_email');
                        if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email.value)) {
                            return (true)
                        } else if (email.value.length == 0) {
                            alert("Please enter your email address!")
                            return (false)
                        } else {
                            alert("You have entered an invalid email address!")
                            return (false)
                        }
                    }

                    function openSupportForm(planType) {
                        query = "Hi!! I am interested in " + planType + " Add-on and want to know more about it.";
                        jQuery("#mo_ldap_licensing_contact_us #query").val(query);
                        jQuery("a[name='licensingContactUs']").click();
                    }

                    function upgradeform(planType) {
                        if (planType == "ContactUs") jQuery("a[name='licensingContactUs']").click();
                        else {
                            jQuery('#requestOrigin').val(planType);
                            if (jQuery('#mo_customer_registered').val() == 1) jQuery('#loginform').submit();
                            else {
                                location.href = jQuery('#mo_backto_ldap_accountsetup_tab').attr('href');
                            }
                        }
                    }
                    jQuery("input[name=sitetype]:radio").change(function () {
                        if (this.value == 'multisite') {
                            jQuery('#multisite_plans').addClass('is-visible').css("display", "grid");
                            jQuery('#singlesite_plans').removeClass('is-visible').css("display", "none");
                        }
                        if (this.value == 'singlesite') {
                            jQuery('#multisite_plans').removeClass('is-visible').css("display", "none");
                            jQuery('#singlesite_plans').addClass('is-visible').css("display", "grid");
                            // jQuery('.momslp#multisite').removeClass('is-visible').addClass('is-hidden');
                            // jQuery('.mosslp#standard').addClass('is-visible').removeClass('is-hidden is-selected');
                        }
                    });
                    jQuery(document).ready(function ($) {
                        //document.getElementById("multisite").checked = true;
                        if (jQuery('#mo_license_plan_selected').val() == 'multisite') {
                            document.getElementById("multisite").checked = true;
                        }
                        if (document.getElementById("multisite").checked == true) {
                            jQuery('.mosslp').removeClass('is-visible').addClass('is-hidden');
                            jQuery('.momslp').addClass('is-visible').removeClass('is-hidden is-selected');
                        }
                        //hide the subtle gradient layer (.cd-pricing-list > li::after) when pricing table has been scrolled to the end (mobile version only)
                        checkScrolling($('.cd-pricing-body'));
                        $(window).on('resize', function () {
                            window.requestAnimationFrame(function () {
                                checkScrolling($('.cd-pricing-body'))
                            });
                        });
                        $('.cd-pricing-body').on('scroll', function () {
                            var selected = $(this);
                            window.requestAnimationFrame(function () {
                                checkScrolling(selected)
                            });
                        });

                        function checkScrolling(tables) {
                            tables.each(function () {
                                var table = $(this),
                                    totalTableWidth = parseInt(table.children('.cd-pricing-features').width()),
                                    tableViewport = parseInt(table.width());
                                if (table.scrollLeft() >= totalTableWidth - tableViewport - 1) {
                                    table.parent('li').addClass('is-ended');
                                } else {
                                    table.parent('li').removeClass('is-ended');
                                }
                            });
                        }
                        //switch from monthly to annual pricing tables
                        bouncy_filter($('.cd-pricing-container'));

                        function bouncy_filter(container) {
                            container.each(function () {
                                var pricing_table = $(this);
                                var filter_list_container = pricing_table.children('.cd-pricing-switcher'),
                                    filter_radios = filter_list_container.find('input[type="radio"]'),
                                    pricing_table_wrapper = pricing_table.find('.cd-pricing-wrapper');
                                //store pricing table items
                                var table_elements = {};
                                filter_radios.each(function () {
                                    var filter_type = $(this).val();
                                    table_elements[filter_type] = pricing_table_wrapper.find('li[data-type="' + filter_type + '"]');
                                });
                                //detect input change event
                                filter_radios.on('change', function (event) {
                                    event.preventDefault();
                                    //detect which radio input item was checked
                                    var selected_filter = $(event.target).val();
                                    //give higher z-index to the pricing table items selected by the radio input
                                    show_selected_items(table_elements[selected_filter]);
                                    //rotate each cd-pricing-wrapper
                                    //at the end of the animation hide the not-selected pricing tables and rotate back the .cd-pricing-wrapper
                                    // if( !Modernizr.cssanimations ) {
                                    //     hide_not_selected_items(table_elements, selected_filter);
                                    //     pricing_table_wrapper.removeClass('is-switched');
                                    // } else {
                                    pricing_table_wrapper.addClass('is-switched').eq(0).one('webkitAnimationEnd oanimationend msAnimationEnd animationend', function () {
                                        hide_not_selected_items(table_elements, selected_filter);
                                        pricing_table_wrapper.removeClass('is-switched');
                                        //change rotation direction if .cd-pricing-list has the .cd-bounce-invert class
                                        if (pricing_table.find('.cd-pricing-list').hasClass('cd-bounce-invert')) pricing_table_wrapper.toggleClass('reverse-animation');
                                    });
                                    // }
                                });
                            });
                        }

                        function show_selected_items(selected_elements) {
                            selected_elements.addClass('is-selected');
                        }

                        function hide_not_selected_items(table_containers, filter) {
                            $.each(table_containers, function (key, value) {
                                if (key != filter) {
                                    $(this).removeClass('is-visible is-selected').addClass('is-hidden');
                                } else {
                                    $(this).addClass('is-visible').removeClass('is-hidden is-selected');
                                }
                            });
                        }
                    });   
                    
                </script>
                <script>
                    function onMouseEnterPlans(){
                        document.getElementById('plans-section').style.borderBottom = '3px solid #e67e22';
                    }
                    function onMouseLeavePlans(){
                        document.getElementById('plans-section').style.borderBottom = 'none';
                    }

                    function onMouseEnterFeatures(){
                        document.getElementById('features-section').style.borderBottom = '3px solid #e67e22';
                    }
                    function onMouseLeaveFeatures(){
                        document.getElementById('features-section').style.borderBottom = 'none';
                    }

                    function onMouseEnterUpgrade(){
                        document.getElementById('upgrade-section').style.borderBottom = '3px solid #e67e22';
                    }
                    function onMouseLeaveUpgrade(){
                        document.getElementById('upgrade-section').style.borderBottom = 'none';
                    }

                    function onMouseEnterAddons(){
                        document.getElementById('addons-section').style.borderBottom = '3px solid #e67e22';
                    }
                    function onMouseLeaveAddons(){
                        document.getElementById('addons-section').style.borderBottom = 'none';
                    }
                </script>
            <?php
}