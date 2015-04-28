<div class="wrap">
    <h2><?php
    _e("ORBTR Forms", "orbtrfields");
        ?>
        <a class="button add-new-h2" href="admin.php?page=orbtrfields&view=edit&id=0"><?php _e("Add New", "orbtrfields") ?></a>
    </h2>
    
    <form id="feed_form" method="post">
        <?php wp_nonce_field('list_action', 'gf_orbtr_list') ?>
        <input type="hidden" id="action" name="action"/>
        <input type="hidden" id="action_argument" name="action_argument"/>
        
        <div class="tablenav">
            <div class="alignleft actions" style="padding:8px 0 7px 0;">
                <label class="hidden" for="bulk_action"><?php _e("Bulk action", "orbtrfields") ?></label>
                <select name="bulk_action" id="bulk_action">
                    <option value=''> <?php _e("Bulk action", "orbtrfields") ?> </option>
                    <option value='delete'><?php _e("Delete", "orbtrfields") ?></option>
                </select>
                <?php
                echo '<input type="submit" class="button" value="' . __("Apply", "orbtrfields") . '" onclick="if( jQuery(\'#bulk_action\').val() == \'delete\' && !confirm(\'' . __("Delete selected feeds? ", "orbtrfields") . __("\'Cancel\' to stop, \'OK\' to delete.", "orbtrfields") .'\')) { return false; } return true;"/>';
                ?>
            </div>
        </div>
    </form>
    
    <table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                <th scope="col" id="active" class="manage-column check-column"></th>
                <th scope="col" class="manage-column"><?php _e("Form", "orbtrfields") ?></th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
                <th scope="col" id="active" class="manage-column check-column"></th>
                <th scope="col" class="manage-column"><?php _e("Form", "orbtrfields") ?></th>
            </tr>
        </tfoot>
        
        <tbody class="list:user user-list">
           <?php if (is_array($settings) && sizeof($settings) > 0): foreach ($settings as $setting): ?> 
           <tr class='author-self status-inherit' valign="top">
           	<th scope="row" class="check-column"><input type="checkbox" name="feed[]" value="<?php echo $setting["id"] ?>"/></th>
           	<td><img src="<?php echo orbtr_url(); ?>/assets/images/active<?php echo intval($setting["is_active"]) ?>.png" alt="<?php echo $setting["is_active"] ? __("Active", "orbtrfields") : __("Inactive", "orbtrfields");?>" title="<?php echo $setting["is_active"] ? __("Active", "orbtrfields") : __("Inactive", "orbtrfields");?>" onclick="ToggleActive(this, <?php echo $setting['id'] ?>); " /></td>
            	<td class="column-title">
                    <a href="admin.php?page=orbtrfields&view=edit&id=<?php echo $setting["id"] ?>" title="<?php _e("Edit", "orbtrfields") ?>"><?php echo $setting["form_title"] ?></a>
                    <div class="row-actions">
                        <span class="edit">
                        <a title="<?php _e("Edit", "orbtrfields")?>" href="admin.php?page=orbtrfields&view=edit&id=<?php echo $setting["id"] ?>" title="<?php _e("Edit", "orbtrfields") ?>"><?php _e("Edit", "orbtrfields") ?></a>
                        |
                        </span>
                        <span>
                        <a title="<?php _e("View Entries", "orbtrfields")?>" href="admin.php?page=gf_entries&view=entries&id=<?php echo $setting["form_id"] ?>" title="<?php _e("View Entries", "orbtrfields") ?>"><?php _e("Entries", "orbtrfields") ?></a>
                        |
                        </span>
                        <span>
                        <a title="<?php _e("Delete", "orbtrfields") ?>" href="javascript: if(confirm('<?php _e("Delete this feed? ", "orbtrfields") ?> <?php _e("\'Cancel\' to stop, \'OK\' to delete.", "orbtrfields") ?>')){ DeleteSetting(<?php echo $setting["id"] ?>);}"><?php _e("Delete", "orbtrfields")?></a>
                        </span>
                    </div>
                </td>
           </tr>
           <?php endforeach; else: ?>
            <tr>
                <td colspan="3" style="padding:20px;">
                    <?php echo sprintf(__("You don't have any ORBTR feeds configured. Let's go %screate one%s!", "orbtrfields"), '<a href="admin.php?page=orbtrfields&view=edit&id=0">', "</a>"); ?>
                </td>
            </tr>
			<?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
	function DeleteSetting(id){
		jQuery("#action_argument").val(id);
		jQuery("#action").val("delete");
		jQuery("#feed_form")[0].submit();
	}
	function ToggleActive(img, feed_id){
		var is_active = img.src.indexOf("active1.png") >=0
		if(is_active){
			img.src = img.src.replace("active1.png", "active0.png");
			jQuery(img).attr('title','<?php _e("Inactive", "orbtrfields") ?>').attr('alt', '<?php _e("Inactive", "orbtrfields") ?>');
		}
		else{
			img.src = img.src.replace("active0.png", "active1.png");
			jQuery(img).attr('title','<?php _e("Active", "orbtrfields") ?>').attr('alt', '<?php _e("Active", "orbtrfields") ?>');
		}

		var mysack = new sack(ajaxurl);
		mysack.execute = 1;
		mysack.method = 'POST';
		mysack.setVar( "action", "gf_orbtr_update_feed_active" );
		mysack.setVar( "gf_orbtr_update_feed_active", "<?php echo wp_create_nonce("gf_orbtr_update_feed_active") ?>" );
		mysack.setVar( "feed_id", feed_id );
		mysack.setVar( "is_active", is_active ? 0 : 1 );
		mysack.encVar( "cookie", document.cookie, false );
		mysack.onError = function() { alert('<?php _e("Ajax error while updating feed", "orbtrfields" ) ?>' )};
		mysack.runAJAX();

		return true;
	}


</script>