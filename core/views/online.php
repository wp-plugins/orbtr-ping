<style type="text/css">label { font-weight: bold; } </style>
<div class="wrap">
    <div id="icon-orbtr" class="icon32"><br /></div>
    <h2><?php _e(ORBTR_PAGE_ONLINE, 'orbtr_ping'); ?></h2>
    <br class="clear" />
	<?php if (!empty($online_leads)): ?>
    <table class="orbtr-list-table post fixed" cellspacing="0">
    <thead>
        <tr>
            <th scope="col" class="manage-column column-email" style="">Visitor</th>
            <th scope="col" class="manage-column column-name" style="">Email</th>
            <th scope="col" class="manage-column column-watch" style="">Orbits</th>
			<th scope="col" class="manage-column column-views" style="">Last Visit</th>
            <th scope="col" class="manage-column column-views" style="">Page Views</th>
        </tr>
    </thead>
    
    <tfoot>
        <tr>
            <th scope="col" class="manage-column column-email" style="">Visitor</th>
            <th scope="col" class="manage-column column-name" style="">Email</th>
            <th scope="col" class="manage-column column-watch" style="">Orbits</th>
			<th scope="col" class="manage-column column-views" style="">Last Visit</th>
            <th scope="col" class="manage-column column-views" style="">Page Views</th>
        </tr>
    </tfoot>
    
    <tbody>
        <?php $i=0; foreach ($online_leads as $online_lead): $i++; ?>
        
        <tr<?php if ($i % 2): ?> class="alternate"<?php endif; ?>>
            <td class="post-title column-email">
            	<?php
				$name = trim($online_lead->fName .' '. $online_lead->lName);
				$name = empty($name) ? 'Visitor '.$online_lead->id : $name;
				?>
                <a href="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $online_lead->id; ?>"><?php echo $name; ?></a>
                <div class="row-actions">
                	<span class="edit"><a href="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $online_lead->id; ?>" title="View Profile">View Profile</a> | </span>
                	<span class="trash"><a href="admin.php?page=orbtrping-allleads&amp;action=delete&amp;uid=<?php echo $lead->id; ?>" class="trash delete submitdelete">Delete Lead</a></span>
                </div>
            </td>
            <td class="post-title column-name"><?php echo $online_lead->email; ?></td>
            <td class="post-title column-watch">
               <?php 
			   		try {
					$orbits = $this->api->getAssignedOrbits($online_lead->id);
					} catch (Orbtr_Exception $e){}
					if (!empty($orbits)): foreach ($orbits as $orbit):
				?>
				<span class="orbit-sprite orbit-sprite-<?php echo $orbit->orbit_color; ?> alignleft" title="<?php echo $orbit->orbit_name; ?>"></span>
				<?php endforeach; endif; ?>
            </td>
			<td class="post-title column-views">
			 	<?php echo mysql2date('M d, Y', $online_lead->lastViewed); ?>
            </td>
            <td class="post-title column-views">
                <?php echo $online_lead->views; ?>
            </td>
        </tr>
    
        <?php endforeach; ?>
    </tbody>
    </table>
    <br /><br />
    <?php else: ?>
    <p>No one is online.</p>
    <?php endif; ?>
</div>