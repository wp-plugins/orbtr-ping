<style type="text/css">label { font-weight: bold; } </style>
<div class="wrap">
    <div id="icon-orbtr" class="icon32"><br /></div>
    <h2><?php _e(ORBTR_PAGE_PROFILE, 'orbtr_ping'); ?></h2>
    <br class="clear" />
	<div id="lead-info" class="cf">
    	<div id="lead-info-header" class="cf">
        	<div class="orbtr-avatar">
            	<?php
				$email = trim($views->email);
   				$email = strtolower($email);
  				$email_hash = md5($email);
				
				$default = orbtr_url().'/assets/admin/images/astronaut.png';
  				?>
				<?php
				try {
				$orbits = $this->api->getAssignedOrbits($_GET['uid'], 1);
				} catch (Orbtr_Exception $e){}
				$extra_class = ' orbit-tab-3';
				if (!empty($orbits)) {
					$extra_class = ' orbit-tab-'.$orbits[0]->orbit_color;
				}
				?>
            	<span class="gravatar-overlay<?php echo $extra_class; ?>"></span>
                <img src="http://www.gravatar.com/avatar/<?php echo $email_hash?>?s=65&d=<?php echo $default; ?>" />
            </div>
        	<?php $name = (empty($views->fName) && empty($views->lName) ? $views->visitor_name : $views->fName .' '.$views->lName); ?>
        	<h3><?php echo $name; ?> <span><?php echo $views->email; ?></span></h3>
            <ul class="header-details cf">
            	<li><span>First Visit</span> <?php echo date('m/d/Y', strtotime($views->first_view)); ?></li>
                <li><span>Total Visits</span> <?php echo $views->total; ?></li>
                <li><span>Total Pages</span> <?php echo $views->pages; ?></li>
                <li><span>Pgs/Visit</span> <?php echo round(($views->pages / $views->total), 1); ?></li>
            </ul>
        </div>
        <table>
        	<tr>
            	<td class="orbtr-info-edit">
					<?php
						try {
						$orbits = $this->api->getAssignedOrbits($_GET['uid'], -1);
						} catch (Orbtr_Exception $e){}
						if (!empty($orbits)): 
					?> 
					<h3 class="orbit-label">Orbits: </h3>
					<ul class="orbit-list">
					<?php foreach ($orbits as $orbit):?>
					<li class="orbit-label-<?php echo $orbit->orbit_color; ?>"><a href="admin.php?page=orbtrping-orbits&action=edit&id=<?php echo $orbit->id; ?>" title="Individual added to this Orbit on <?php echo date('m/d/Y', strtotime($orbit->createDate)); ?>"><span class="orbit-sprite orbit-sprite-<?php echo $orbit->orbit_color; ?> alignleft"></span><?php echo $orbit->orbit_name; ?></a></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
                	<form action="admin.php?page=<?php echo $_GET['page']; ?>&amp;action=views&amp;uid=<?php echo $_GET['uid']; ?>" method="post">
						<input type="hidden" name="updatelead" value="1" />
                    	<dl>
                        	<dt>First Name</dt>
							<dd><input type="text" name="first_name" value="<?php echo $views->fName; ?>" /></dd>
							<dt>Last Name</dt>
							<dd><input type="text" name="last_name" value="<?php echo $views->lName; ?>" /></dd>
							<dt>Email</dt>
							<dd><input type="text" name="email" value="<?php echo $views->email; ?>" /></dd>
							<dt>Company</dt>
							<dd><input type="text" name="company" value="<?php echo $views->company; ?>" /></dd>
							<dt>Notes</dt>
							<dd><textarea name="lead_notes"><?php echo htmlentities(stripslashes($views->notes)); ?></textarea></dd>
						</dl>
						<p><input type="submit" name="update-lead" value="Save Changes" class="button orbtr-button no-round" /></p>
                    </form>
					  <?php if (isset($views->email) && !empty($views->email)): ?>
					  <p><a href="http://www.jigsaw.com/FreeTextSearch.xhtml?opCode=search&autoSuggested=true&freeText=<?php echo urlencode($views->email); ?>" class="button orbtr-button grey-button no-round" target="_blank">Lookup on Data.com</a></p>
					  <p><a href="mailto:<?php echo $views->email; ?>" class="button orbtr-button grey-button no-round" target="_blank">Send Email</a></p>
					  <?php endif; ?>
                </td>
                <td class="orbtr-info-views">
                	<?php foreach ($views->views->viewdata as $date => $data): ?>
					<?php
					$viewInfo = $data->info;
                    $dateTime = new DateTime ($date);
					$refHelper = new RefererHelper($viewInfo->referer);
					$keywords = implode(" ", (array)$refHelper->getKeywords());
					$country = $viewInfo->country;
					$uaHelper = new UserAgentParser($viewInfo->ua);
					$browser = $uaHelper->getBrowser();
					$browser_class = strtolower(str_replace(' ', '-', $browser['name']));
					$os = $uaHelper->getOS();
					$os_class = strtolower(str_replace(array(' ','/','.'), '-', $os));
					$keywords = $refHelper->getKeywords();
                    ?>
                    <div class="orbtr-views-day cf">
                    	<span class="orbtr-day">
                        	<span class="date-wrap">
                                <span class="month"><?php echo $dateTime->format('M'); ?></span>
                                <span class="day"><?php echo $dateTime->format('j'); ?></span>
                                <span class="year"><?php echo $dateTime->format('Y'); ?></span>
                            </span>
                        </span>
                        <?php
                            $loc = array_map('trim', explode(',', $viewInfo->citystate));
                            if (isset($loc[1]) && (strtolower(trim($country)) != 'us' && strtolower(trim($country)) != 'ca')) $loc[1] = strtoupper($country);
                            $viewInfo->citystate = implode(', ', $loc);
                        ?>
                        <div class="orbtr-view-details orbtr-main-details">
                            <table>
                                <tr>
                                    <td>
                                        <h4><span>Referal Source:</span> <?php echo $refHelper->getReferalHost(); ?></h4>
                                        <div class="orbtr-detail-icons">
                                            <span><?php echo $viewInfo->citystate; ?></span>
                                            <?php if ($country && strlen($country) == 2): ?>
                                            <span class="flags-icon flags-<?php echo $country; ?>" title="<?php echo strtoupper($country); ?>"></span>
                                            <?php endif; ?>
                                            <span class="browser-icon browser-<?php echo $browser_class; ?>" title="<?php echo $browser['name']; ?>"></span>
                                            <span class="os-icon os-<?php echo $os_class; ?>" title="<?php echo $os; ?>"></span>
                                        </div>
                                        <br class="clear" />
                                    </td>
                            </table>
                        </div>
                        <ul>
                        	<?php $i =0; foreach ($data->pages as $view): $i++; ?>
                            <?php
							$dateTime = new DateTime ($view->viewTime);
							?>
                            <li<?php if ($i % 2): ?> class="alt"<?php endif; ?>>
                            	<?php echo $view->pageName; ?> <span class="date-text"><?php echo $dateTime->format('h:i a'); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="orbtr-view-details cf">
                        	<h4>Visit Info</h4>
                            <p><span>Company Network/ISP</span> <?php echo ($viewInfo->company) ? $viewInfo->company : 'n/a'; ?></p>
                            <p><span>Browser</span> <?php echo ($browser['name']) ? $browser['name'] .' '.$browser['version'] : 'n/a'; ?></p>
                            <p><span>Computer/OS</span> <?php echo ($os) ? $os : 'n/a'; ?></p>
                            <p><span>Screen Resolution</span> <?php echo ($viewInfo->screen) ? $viewInfo->screen : 'n/a'; ?></p>
                            <p><span>IP Address</span> <?php echo ($viewInfo->ip) ? $viewInfo->ip : 'n/a'; ?></p>
                            <p><span>Referer:</span> <?php echo ($viewInfo->referer) ? $viewInfo->referer : 'n/a'; ?></p>
							   <p><span>Keywords:</span> <?php echo (isset($keywords['keywords'])) ? $keywords['keywords'] : 'n/a'; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="tablenav">
                    <?php echo $pager->pageList($_GET['p'], $pages, $count); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>