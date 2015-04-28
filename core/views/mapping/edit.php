<style>
    #orbtr_submit_container{clear:both;}
    .orbtr_col_heading{padding-bottom:2px; border-bottom: 1px solid #ccc; font-weight:bold; width:120px;}
    .orbtr_field_cell {padding: 6px 17px 0 0; margin-right:15px;}

    .orbtr_validation_error{ background-color:#FFDFDF; margin-top:4px; margin-bottom:6px; padding-top:6px; padding-bottom:6px; border:1px dotted #C89797;}
    .orbtr_validation_error span {color: red;}
    .left_header{float:left; width:200px;}
    .margin_vertical_10{margin: 10px 0; padding-left:5px;}
    .margin_vertical_30{margin: 30px 0; padding-left:5px;}
    .width-1{width:300px;}
    .gf_orbtr_invalid_form{margin-top:30px; background-color:#FFEBE8;border:1px solid #CC0000; padding:10px; width:600px;}
</style>

<script type="text/javascript" src="<?php echo GFCommon::get_base_url()?>/js/gravityforms.js"> </script>

<div class="wrap">
    <h2><?php
    _e("ORBTR Fields", "orbtrfields");
        ?>
    </h2>
    
    <?php
		//getting setting id (0 when creating a new one)
		$id = !empty($_POST["orbtr_setting_id"]) ? $_POST["orbtr_setting_id"] : absint($_GET["id"]);
		$config = empty($id) ? array("meta" => array(), "is_active" => true) : ORBTRMappingData::get_feed($id);
		
		//updating meta information
        if(rgpost("gf_orbtr_submit")){

            $config["form_id"] = absint(rgpost("gf_orbtr_form"));

            //-----------------

            $customer_fields = self::get_orbtr_fields();
			
            $config["meta"]["orbtr_fields"] = array();
            foreach($customer_fields as $field){
                $config["meta"]["orbtr_fields"][$field["name"]] = $_POST["orbtr_field_{$field["name"]}"];
            }
			
            $config = apply_filters('gform_orbtr_save_config', $config);

            $id = ORBTRMappingData::update_feed($id, $config["form_id"], $config["is_active"], $config["meta"]);
			?>
			<div class="updated fade" style="padding:6px"><?php echo sprintf(__("Feed Updated. %sback to list%s", "orbtrfields"), "<a href='?page=orbtrfields'>", "</a>") ?></div>
			<?php
        }
		
		$form = isset($config["form_id"]) && $config["form_id"] ? $form = RGFormsModel::get_form_meta($config["form_id"]) : array();
		$settings = get_option("gf_orbtr_settings");
	?>
    
    <form method="post" action="">
		<input type="hidden" name="orbtr_setting_id" value="<?php echo $id ?>" />
       <div id="orbtr_form_container" valign="top" class="margin_vertical_10">
            <label for="gf_orbtr_form" class="left_header"><?php _e("Gravity Form", "orbtrfields"); ?> <?php gform_tooltip("orbtr_gravity_form") ?></label>
            
            <select id="gf_orbtr_form" name="gf_orbtr_form" onchange="SelectForm(jQuery(this).val(), '<?php echo rgar($config, 'id') ?>');">
                <option value=""><?php _e("Select a form", "orbtrfields"); ?> </option>
                <?php
            
                $active_form = rgar($config, 'form_id');
                $available_forms = ORBTRMappingData::get_available_forms($active_form);
            
                foreach($available_forms as $current_form) {
                    $selected = absint($current_form->id) == rgar($config, 'form_id') ? 'selected="selected"' : '';
                    ?>
            
                        <option value="<?php echo absint($current_form->id) ?>" <?php echo $selected; ?>><?php echo esc_html($current_form->title) ?></option>
            
                    <?php
                }
                ?>
            </select>
            &nbsp;&nbsp;
            <img src="<?php echo orbtr_url();  ?>/assets/images/loading.gif" id="orbtr_wait" style="display: none;"/>
            
            <div id="gf_orbtr_invalid_creditcard_form" class="gf_orbtr_invalid_form" style="display:none;">
                <?php _e("The form selected does not have a credit card field. Please add a credit card field to the form and try again.", "orbtrfields") ?>
            </div>
		</div>
        <div id="orbtr_field_group" valign="top" <?php echo empty($config["form_id"]) ? "style='display:none;'" : "" ?>>
			<div id="orbtr_field_container_subscription" class="orbtr_field_container" valign="top">
                <div class="margin_vertical_10">
                    <label class="left_header"><?php _e("ORBTR Fields", "orbtrfields"); ?> <?php gform_tooltip("orbtr_fields") ?></label>
                
                    <div id="orbtr_customer_fields">
                        <?php
                            if(!empty($form))
                                echo self::get_orbtr_information($form, $config);
                        ?>
                    </div>
                </div>
                
                <div id="orbtr_submit_container" class="margin_vertical_30">
                    <input type="submit" name="gf_orbtr_submit" value="<?php echo empty($id) ? __("  Save  ", "orbtrfields") : __("Update", "orbtrfields"); ?>" class="button-primary"/>
                    <input type="button" value="<?php _e("Cancel", "orbtrfields"); ?>" class="button" onclick="javascript:document.location='admin.php?page=orbtrfields'" />
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">

	<?php
	if(!empty($config["form_id"])){
		?>

		// initiliaze form object
		form = <?php echo GFCommon::json_encode($form)?>;

		<?php
	}
	?>

	function SelectForm(formId, settingId){
		if(!formId){
			jQuery("#orbtr_field_group").slideUp();
			return;
		}

		jQuery("#orbtr_wait").show();
		jQuery("#orbtr_field_group").slideUp();

		var mysack = new sack(ajaxurl);
		mysack.execute = 1;
		mysack.method = 'POST';
		mysack.setVar( "action", "gf_select_orbtr_form" );
		mysack.setVar( "gf_select_orbtr_form", "<?php echo wp_create_nonce("gf_select_orbtr_form") ?>" );
		mysack.setVar( "form_id", formId);
		mysack.setVar( "setting_id", settingId);
		mysack.encVar( "cookie", document.cookie, false );
		mysack.onError = function() {jQuery("#orbtr_wait").hide(); alert('<?php _e("Ajax error while selecting a form", "orbtrfields") ?>' )};
		mysack.runAJAX();

		return true;
	}

	function EndSelectForm(form_meta, customer_fields, contact_fields){
		//setting global form object
		form = form_meta;

		jQuery(".gf_orbtr_invalid_form").hide();
		
		/*if(GetFieldsByType(["creditcard"]).length == 0){
			jQuery("#gf_orbtr_invalid_creditcard_form").show();
			jQuery("#orbtr_wait").hide();
			return;
		}*/

		jQuery(".orbtr_field_container").hide();
		jQuery("#orbtr_customer_fields").html(customer_fields);

		jQuery("#gf_orbtr_update_post").attr("checked", false);
		jQuery("#orbtr_post_update_action").hide();

		//Calling callback functions
		jQuery(document).trigger('orbtrFormSelected', [form]);

		jQuery("#orbtr_field_container_subscription").show();
		jQuery("#orbtr_field_group").slideDown();
		jQuery("#orbtr_wait").hide();
	}

	function GetFieldsByType(types){
		var fields = new Array();
		for(var i=0; i<form["fields"].length; i++){
			if(IndexOf(types, form["fields"][i]["type"]) >= 0)
				fields.push(form["fields"][i]);
		}
		return fields;
	}

	function IndexOf(ary, item){
		for(var i=0; i<ary.length; i++)
			if(ary[i] == item)
				return i;

		return -1;
	}

	function GetFieldValues(fieldId, selectedValue, labelMaxCharacters){
		if(!fieldId)
			return "";

		var str = "";
		var field = GetFieldById(fieldId);
		if(!field)
			return "";

		var isAnySelected = false;

		if(field["type"] == "post_category" && field["displayAllCategories"]){
			str += '<?php $dd = wp_dropdown_categories(array("class"=>"optin_select", "orderby"=> "name", "id"=> "gf_authorizenet_conditional_value", "name"=> "gf_authorizenet_conditional_value", "hierarchical"=>true, "hide_empty"=>0, "echo"=>false)); echo str_replace("\n","", str_replace("'","\\'",$dd)); ?>';
		}

		return str;
	}

	function GetFieldById(fieldId){
		for(var i=0; i<form.fields.length; i++){
			if(form.fields[i].id == fieldId)
				return form.fields[i];
		}
		return null;
	}

	function GetSelectableFields(selectedFieldId, labelMaxCharacters){
		var str = "";
		var inputType;
		for(var i=0; i<form.fields.length; i++){
			fieldLabel = form.fields[i].adminLabel ? form.fields[i].adminLabel : form.fields[i].label;
			fieldLabel = typeof fieldLabel == 'undefined' ? '' : fieldLabel;
			inputType = form.fields[i].inputType ? form.fields[i].inputType : form.fields[i].type;
			if (IsConditionalLogicField(form.fields[i])) {
				var selected = form.fields[i].id == selectedFieldId ? "selected='selected'" : "";
				str += "<option value='" + form.fields[i].id + "' " + selected + ">" + TruncateMiddle(fieldLabel, labelMaxCharacters) + "</option>";
			}
		}
		return str;
	}

	function IsConditionalLogicField(field){
		inputType = field.inputType ? field.inputType : field.type;
		var supported_fields = ["checkbox", "radio", "select", "text", "website", "textarea", "email", "hidden", "number", "phone", "multiselect", "post_title",
								"post_tags", "post_custom_field", "post_content", "post_excerpt"];

		var index = jQuery.inArray(inputType, supported_fields);

		return index >= 0;
	}

</script>