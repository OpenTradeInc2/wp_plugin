<div class="wrap">
    <h4>Open Trade 2.0</h4>
    <h3>Inventory list loaded</h3>
    <p>Clic the button search</p>
    <br>
    <form action="" method="POST">
        <input type="submit" name="search_draf_post" value="Search" class="button-primary">
    </form>
    <br>
    <table class="widefat">
        <thead>
        <tr>
            <th>Post Title</th>
            <th>Post ID</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>Post Title</th>
            <th>Post ID</th>
        </tr>
        </tfoot>
        <tbody>
        <?php
        global $wpdb;
        $mytestdrafts = array();
        if(isset($_POST['search_draf_post'])) {
            $mytestdrafts = $wpdb->get_results(
                "
                        SELECT ID, post_title
                        FROM $wpdb->posts
                        WHERE post_status ='publish'
                        "
            );

            update_option('myfirstplugin_draft_post', $mytestdrafts);
        }
        else if(get_option('myfirstplugin_draft_post'))
        {
            $mytestdrafts = get_option('myfirstplugin_draft_post');
        }
        foreach ($mytestdrafts as $mytestdraft) {
            ?>
            <tr>
                <?php
                echo "<td>" . $mytestdraft->post_title . "</td>";
                echo "<td>" . $mytestdraft->ID . "</td>";
                ?>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

<?php
function we() {
echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>Pending Approval Files</h2>';
    echo '</div>';
}
?>