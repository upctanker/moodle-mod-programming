<?php

/// Print the page header
    $pagename = get_string('detailreport', 'programming'); 
    include_once('pageheader.php');

/// Print tabs
    $currenttab = 'reports';
    $currenttab2 = 'detail';
    include_once('tabs.php');

/// Print the main part of the page
?>

<div class="maincontent generalbox">

<table><tr><td>
<?php
    print_group_menu($groups, $groupmode, $groupid, 'reports_detail.php?a='.$programming->id.'&amp;latestonly='.$latestonly.'&amp;orderby='.$orderby.'&amp;firstinitial='.$firstinitial.'&amp;lastinitial='.$lastinitial);
?>
</td>
<td><?php print_single_button('reports_detail.php', array('a' => $programming->id, 'group' => $groupid, 'latestonly' => ($latestonly ? 0 : 1), 'page' => $page, 'orderby' => $orderby, 'firstinitial' => $firstinitial, 'lastinitial' => $lastinitial), get_string(($latestonly ? 'showall' : 'showlatestonly'), 'programming')); ?></td>
</tr></table>

<?php
    $strall = get_string('all');
    $alphabet = explode(',', get_string('alphabet'));

    echo "<p style=\"text-align:center\">";
    $ne = get_string('nameedit', 'langconfig');
    for ($i = 0; $i < strlen($ne); $i++) {
        if ($i > 0) echo '<br />';
        if (substr($ne, $i, 1) == 'F') {
            /// Bar of first initials

            echo get_string("firstname")." : ";
            if ($firstinitial) {
                echo " <a href=\"reports_detail.php?a=$a&amp;".
                     "latestonly=$latestonly&amp;orderby=$orderby&amp;".
                     "group=$groupid&amp;lastinitial=$lastinitial".
                     "\">$strall</a> ";
            } else {
                echo " <b>$strall</b> ";
            }
            foreach ($alphabet as $letter) {
                if ($letter == $firstinitial) {
                    echo " <b>$letter</b> ";
                } else {
                    echo " <a href=\"reports_detail.php?a=$a&amp;".
                         "latestonly=$latestonly&amp;orderby=$orderby&amp;".
                         "group=$groupid&amp;lastinitial=$lastinitial&amp;".
                         "firstinitial=$letter\">$letter</a> ";
                }
            }

        } else if (substr($ne, $i, 1) == 'L') {

            /// Bar of last initials

            echo get_string("lastname")." : ";
            if ($lastinitial) {
                echo " <a href=\"reports_detail.php?a=$a&amp;".
                     "latestonly=$latestonly&amp;orderby=$orderby&amp;".
                     "group=$groupid&amp;firstinitial=$firstinitial".
                     "\">$strall</a> ";
            } else {
                echo " <b>$strall</b> ";
            }
            foreach ($alphabet as $letter) {
                if ($letter == $lastinitial) {
                    echo " <b>$letter</b> ";
                } else {
                    echo " <a href=\"reports_detail.php?a=$a&amp;".
                         "latestonly=$latestonly&amp;orderby=$orderby&amp;".
                         "group=$groupid&amp;firstinitial=$firstinitial&amp;".
                         "lastinitial=$letter\">$letter</a> ";
                }
            }

        } // if
    } // for nameedit
    echo "</p>";
?>

<?php if (is_array($usersubmits) and !empty($usersubmits)): ?>
<table class="generaltable generalbox">
<tbody>
<tr>
<th><?php echo get_string('fullname'); ?></th>
<th>
<a href="<?php echo newme(array('orderby' => $orderby !== 'submittime asc' ? 'submittime asc' : 'submittime desc' )); ?>"><?php echo get_string('submittime', 'programming'); ?></a>
<?php if ($orderby === 'submittime asc'): ?>
<img src="<?php echo $CFG->pixpath ?>/t/up.gif" alt="up" />
<?php elseif ($orderby === 'submittime desc'): ?>
<img src="<?php echo $CFG->pixpath ?>/t/down.gif" alt="down" />
<?php endif ?>
</th>
<th><?php echo get_string('programminglanguage', 'programming'); ?></th>
<?php if ($viewotherprogram): ?>
<th>
<a href="<?php echo newme(array('orderby' => $orderby !== 'linecount asc' ? 'linecount asc' : 'linecount desc' )); ?>"><?php echo get_string('programcode', 'programming'); ?></a>
<?php if ($orderby === 'linecount asc'): ?>
<img src="<?php echo $CFG->pixpath ?>/t/up.gif" alt="up" />
<?php elseif ($orderby === 'linecount desc'): ?>
<img src="<?php echo $CFG->pixpath ?>/t/down.gif" alt="down" />
<?php endif ?>
</th>
<?php endif; ?>
<th><?php echo get_string('status', 'programming'); ?></th>
<th><?php echo get_string('testresults', 'programming'); ?></th>
<?php if ($submitforothers): ?>
<th><?php echo get_string('submit', 'programming'); ?></th>
<?php endif; ?>
<?php if ($deleteothersubmit): ?>
<th><?php echo get_string('delete'); ?></th>
<?php endif; ?>
</tr>

<?php
    foreach ($usersubmits as $usersubmit):
        $puid = 0;
        foreach ($usersubmit as $submit):
?>

<tr>
<?php
        if ($puid != $submit->userid):
?>
<td class="cell" rowspan="<?php echo $latestonly ? '1' : count($usersubmit); ?>"><a href="<?php echo $CFG->wwwroot; ?>/user/view.php?id=<?php echo $submit->userid; ?>&amp;course=<?php echo $course->id; ?>"><?php echo fullname($users[$submit->userid]); ?></a></td>
<?php
            endif;
            if (!$latestonly or $puid != $submit->userid):
?>
<td class="cell"><?php echo userdate($submit->timemodified, '%Y-%m-%d %H:%M:%S') ?></td>
<td class="cell"><?php echo $submit->langname?></td>

<?php if ($viewotherprogram): ?>
<td class="cell"><a href="<?php echo $CFG->wwwroot; ?>/mod/programming/history.php?a=<?php echo $programming->id; ?>&amp;userid=<?php echo $submit->userid; ?>"><?php echo get_string('sizelines', 'programming', $submit) ?></a></td>
<?php else: ?>
<td class="cell"><?php echo get_string('sizelines', 'programming', $submit) ?></td>
<?php endif; ?>

<td class="cell"><?php echo programming_get_submit_status_short($submit); ?></td>
<td class="cell">
<?php
                switch ($submit->status):
                    case PROGRAMMING_STATUS_COMPILEFAIL:
                        $r = programming_parse_compile_message($submit->compilemessage);
                        if ($viewotherresult) {
                            echo '<a href="'.$CFG->wwwroot.'/mod/programming/result.php?a='.$programming->id.'&amp;submitid='.$submit->id.'">'.get_string('nwarningerror', 'programming', $r).'</a>';
                        } else {
                            echo get_string('nwarningerror', 'programming', $r);
                        }
                        break;
                    case PROGRAMMING_STATUS_FINISH:
                        if ($programming->showmode == PROGRAMMING_SHOWMODE_NORMAL) {
                            $html = programming_get_test_results_short($submit, $results[$submit->id]);
                        } else {
                            $html = programming_contest_get_judgeresult($results[$submit->id]);
                        }
                        if ($viewotherresult) {
                            echo '<a href="'.$CFG->wwwroot.'/mod/programming/result.php?a='.$programming->id.'&amp;submitid='.$submit->id.'">'.$html.'</a>';
                        } else {
                            echo $html;
                        }
                        break;
                    default:
                        echo '&nbsp;';
                endswitch;
?>
</td>
<?php if ($submitforothers && $puid != $submit->userid): ?>
<td class="cell" rowspan="<?php echo $latestonly ? '1' : count($usersubmit); ?>"><?php print_single_button('view.php', array('a' => $programming->id, 'group' => $groupid, 'submitfor' => $submit->userid), get_string('submit', 'programming')); ?>
<?php endif; ?>
<?php if ($deleteothersubmit): ?>
<td class="cell"><?php print_single_button('deletesubmit.php', array('submitid' => $submit->id), get_string('delete')); ?>
</td>
<?php endif; ?>
</tr>
<?php
                    $puid = $submit->userid;
                endif;
            endforeach;
        endforeach;
?>
</tbody>
</table>
<?php 
    print_paging_bar($totalcount, $page, $perpage, $CFG->wwwroot.'/mod/programming/reports_detail.php?a='.$programming->id.'&amp;latestonly='.$latestonly.'&amp;orderby='.$orderby.'&amp;group='.$groupid.'&amp;firstinitial='.$firstinitial.'&amp;lastinitial='.$lastinitial.'&amp;');

    else:
        echo '<p>'.get_string('nosubmit', 'programming').'</p>';
    endif;
?>

</div>

<?php
/// Finish the page
    print_footer($course);
?>
