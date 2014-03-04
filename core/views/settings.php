<div class="wrap">
    <div id="icon-orbtr" class="icon32"><br /></div>
    <h2><?php echo _e(ORBTR_PAGE_SETTINGS, 'orbtr_ping'); ?></h2>
    <br class="clear" />
    <?php
    global $orbtr_errors;
    $orbtr_errors->printMessages();
    echo $this->forms->startForm('admin.php?page='.$_GET['page'], 'post','', array('enctype'=>'multipart/form-data'));
    echo $this->forms->addInput('hidden', 'action', 'updateOptions');
    ?>
    <ul class="orbtr-accordian">
    <li>
    <h2 class="button trigger"><?php _e(ORBTR_SETUP_TAB, 'orbtr_ping'); ?></h2>
    <div class="orbtr-content">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e(ORBTR_SETUP_KEY, 'orbtr_ping'); ?></th>
            <td><?php echo $this->forms->addInput('text', 'orbtr_api_key', $options['orbtr_api_key'], array('class' => 'text')); ?><?php if (empty($options['orbtr_api_key'])): ?> <span class="description"><a href="http://orbtr.net/ping" target="_blank">Get an API Key</a></span><?php endif; ?></td>
		</tr>
        <tr valign="top">
			<th scope="row"><?php _e(ORBTR_SETUP_ACCOUNT, 'orbtr_ping'); ?></th>
            <td><?php echo $this->forms->addInput('text', 'orbtr_account_id', $options['orbtr_account_id'], array('class' => 'text')); ?></td>
		</tr>
        <?php if (!isset($results->error)): ?>
        <tr valign="top">
			<th scope="row"><?php _e(ORBTR_SETUP_NOTIFY_EMAIL, 'orbtr_ping'); ?></th>
            <td>
				<?php echo $this->forms->addInput('text', 'orbtr_notify_emails', $options['email'], array('class' => 'text')); ?><br />
            	<span class="description"><?php _e(ORBTR_SETUP_NOTIFY_EMAIL_HELP, 'orbtr_ping'); ?></span>    
            </td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e(ORBTR_SETUP_NOTIFY, 'orbtr_ping'); ?></th>
            <td>
				<?php
					$checked = '';
					$checked2 = '';
					if ($options['email_new'] == 1)
					{
						$checked = array('checked' => 'checked');	
					}
					
					if ($options['email_returning'] == 1)
					{
						$checked2 = array('checked' => 'checked');	
					}
                    
                  if ($options['digest_email'] == 1)
					{
						$checked3 = array('checked' => 'checked');	
					}
				?>
				<?php echo $this->forms->addInput('checkbox', 'email_new', 1, $checked); ?> <?php _e(ORBTR_SETUP_NOTIFY_LEADS, 'orbtr_ping'); ?><br />
            	<?php echo $this->forms->addInput('checkbox', 'email_returning', 1, $checked2); ?> <?php _e(ORBTR_SETUP_NOTIFY_RETURN, 'orbtr_ping'); ?><br /> 
                <?php echo $this->forms->addInput('checkbox', 'digest_email', 1, $checked3); ?> <?php _e('Email me daily stats for my site.', 'orbtr_ping'); ?><br />   
            </td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e(ORBTR_SETUP_COMMENTS, 'orbtr_ping'); ?></th>
            <td>
				<?php
					$checked = '';
					$checked2 = '';
					if ($options['orbtr_track_comments'] == 1)
					{
						$checked = array('checked' => 'checked');	
					}
				?>
				<?php echo $this->forms->addInput('checkbox', 'orbtr_track_comments', 1, $checked); ?> <?php _e(ORBTR_SETUP_COMMENTS_ENABLE, 'orbtr_ping'); ?><br />  
            </td>
		</tr>
        <tr valign="top">
        	<th scope="row"><?php _e(ORBTR_SETUP_TIMEZONE, 'orbtr_ping'); ?></th>
            <td>
               <p><?php printf(__(ORBTR_SETUP_TIMEZONE_HELP, 'orbtr_ping'), '<a href="'.admin_url('options-general.php').'">','</a>', '<a href="'.admin_url('options-general.php').'">', '</a>'); ?></p>   
            </td>
        </tr>
        <?php endif; ?>
	</table>
    </div>
    </li>
    <?php do_action('orbtr_settings'); ?>
    <ul>
	<?php
	echo '<p class="submit">'.$this->forms->addInput('submit', 'submit', 'Save Options', array('class' => 'button button-primary')).'</p>';
	echo $this->forms->endForm();
    ?>
</div>