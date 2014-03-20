<style type="text/css">label { font-weight: bold; } </style>
<div class="wrap">
    <div id="icon-orbtr" class="icon32"><br /></div>
    <h2>Edit ORBTR Lead</h2>
    <hr />
    <br class="clear" />
    <?php
	echo $this->form->startForm('admin.php?page='.$_GET['page'].'&amp;action=edit&amp;uid='.(int)$_REQUEST['uid'], 'post','', array('enctype'=>'multipart/form-data'));
	echo $this->form->addInput('hidden', 'action2', 'updateLead');
	?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Email</th>
            <td>
            	<label><?php echo stripslashes($data->email); ?></label> 
                <a href="http://www.jigsaw.com/FreeTextSearch.xhtml?opCode=search&autoSuggested=true&freeText=<?php echo urlencode($data->email); ?>" class="button orbtr-button" target="_blank">Lookup on Data.com</a>
                <a href="mailto:<?php echo $data->email; ?>" class="button orbtr-button" target="_blank">Send Email</a>
                <a href="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $data->id; ?>" title="View History" class="button">View History</a></span>
            </td>
		</tr>
        <tr valign="top">
			<th scope="row">First Name</th>
            <td><?php echo $this->form->addInput('text', 'first_name', stripslashes($data->fName), array('class' => 'text')); ?></td>
		</tr>
        <tr valign="top">
			<th scope="row">Last Name</th>
            <td><?php echo $this->form->addInput('text', 'last_name', stripslashes($data->lName), array('class' => 'text')); ?></td>
		</tr>
        <tr valign="top">
        	<th scope="row">Lead Priority</th>
            <td>
				<?php echo $this->form->addSelectList('lead_priority', array(1 => 'Cold', 2 => 'Warm', 3 => 'Hot'), true, stripslashes($data->priority)); ?><br />
            	<span class="description">User's priority for tracking.</span>    
            </td>
        </tr>
        <tr valign="top">
			<th scope="row">Notes</th>
            <td><?php echo $this->form->addTextarea('lead_notes', 10, 90, htmlspecialchars(stripslashes($data->notes)), array('class' => 'text')); ?></td>
		</tr>
	</table>
    <?php
	echo '<p class="submit">'.$this->form->addInput('submit', 'submit', 'Save Lead', array('class' => 'button button-primary')).'</p>';
	echo $this->form->endForm();
    ?>
</div>