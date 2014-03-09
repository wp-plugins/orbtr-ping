<style type="text/css">label { font-weight: bold; } </style>
<div class="wrap">
    <div id="icon-orbtr" class="icon32"><br /></div>
    <h2><?php _e(ORBTR_PAGE_RECORDS, 'orbtr_ping'); ?></h2>
    <br class="clear" />
	<?php if (!empty($leads->leads->lead)): ?>
    <form action="admin.php" method="get">
    <p class="search-box">
        <label class="screen-reader-text" for="post-search-input">Search Posts:</label>
        <input name="search_text" value="<?php echo $search_text; ?>" type="search">
        <input name="" id="search-submit" class="button" value="Search Leads" type="submit">
    </p>
    <div class="tablenav">
	<div class="alignleft actions">
            <span style="float: left; line-height: 30px;">Sort By: </span>
           <select name="filter">
                <?php foreach ($filters as $name => $value): ?>
               	<option value="<?php echo $value; ?>"<?php if ($value == $filter): ?> selected<?php endif; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
			<?php 
			try {
			$filters = $this->api->listOrbits();
			} catch (Orbtr_Exception $e){}
			if (!empty($filters)):
			?>
			<select name="orbit_filter">
				  <option value="-1">All Orbits</option>
                <?php foreach ($filters as $filter): ?>
               	<option value="<?php echo $filter->id; ?>"<?php if ($filter->id == $orbit_filter): ?> selected<?php endif; ?>><?php echo $filter->orbit_name; ?></option>
                <?php endforeach; ?>
            </select> 
			<?php endif; ?>
			<select name="sort_column">
                <?php foreach ($sort_columns as $name => $value): ?>
               	<option value="<?php echo $value; ?>"<?php if ($value == $sort_column): ?> selected<?php endif; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
			 <select name="sort_order">
                <?php foreach ($sort_orders as $name => $value): ?>
               	<option value="<?php echo $value; ?>"<?php if ($value == $sort_order): ?> selected<?php endif; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
            <input name="" class="button" value="Filter" type="submit" />
	</div>
    <?php echo $pager->pageList($_GET['p'], $pages, $count); ?>
    <br class="clear" />
    </div>
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
        <?php $i=0; foreach ($leads->leads->lead as $lead): $i++; ?>
        
        <tr<?php if ($i % 2): ?> class="alternate"<?php endif; ?>>
            <td class="post-title column-email">
            	<?php
				$name = trim($lead->fName .' '. $lead->lName);
				$name = empty($name) ? 'Visitor '.$lead->id : $name;
				?>
                <a href="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $lead->id; ?>"><?php echo $name; ?></a>
                <div class="row-actions">
                	<span class="edit"><a href="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $lead->id; ?>" title="View Profile">View Profile</a> | </span>
                	<span class="trash"><a href="<?php echo add_query_arg($linkparams, "admin.php?page=orbtrping-allleads&amp;action=delete&amp;uid={$lead->id}"); ?>" class="trash delete submitdelete">Delete Lead</a></span>
                </div>
            </td>
            <td class="post-title column-name"><?php echo $lead->email; ?></td>
            <td class="post-title column-watch">
               <?php 
			   		try {
					$orbits = $this->api->getAssignedOrbits($lead->id);
					} catch (Orbtr_Exception $e){}
					if (!empty($orbits)): foreach ($orbits as $orbit):
				?>
				<span class="orbit-sprite orbit-sprite-<?php echo $orbit->orbit_color; ?> alignleft" title="<?php echo $orbit->orbit_name; ?>"></span>
				<?php endforeach; endif; ?>
			   
            </td>
			 <td class="post-title column-views">
			 	<?php echo mysql2date('M d, Y', $lead->lastViewed); ?>
            </td>
            <td class="post-title column-views">
                <?php echo $lead->views; ?>
            </td>
        </tr>
    
        <?php endforeach; ?>
    </tbody>
    
    </table>
    <div class="tablenav">
    <?php echo $pager->pageList($_GET['p'], $pages, $count); ?>
    <br class="clear" />
    </div>
    </form>
    <?php else: ?>
    <p>No one has visited your site.</p>
    <?php endif; ?>
    <script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			$('a.submitdelete').click(function() {
				return confirm('You are about to delete this Lead. Are you sure you wish to continue? This action cannot be undone.');
			});
		});
		//]]>
	</script>
</div>