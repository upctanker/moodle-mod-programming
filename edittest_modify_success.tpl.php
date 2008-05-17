<div class="generalbox" align="center">
<?php 
    if ($action == get_string('add')) {
        echo get_string('testcaseadded', 'programming');
    } else {
        echo get_string('testcasemodified', 'programming');
    }
    print_single_button('edittest_list.php', array('a' => $programming->id), get_string('continue'));
?>
</div>
