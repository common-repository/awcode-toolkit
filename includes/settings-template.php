<style>
.awbox{
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    width:46%;
    padding: 0.5% 1%;
    margin-right:1%;
    margin-bottom:1%;
    float: left;
    min-height:200px;
}
</style>

<div class="wrap">
    <h1>AWcode Toolbox</h1>
	<form method="post" action="<?=admin_url( 'options-general.php?page=awtoolbox')?>">
        <input type="hidden" name="action" value="updating-awtoolkit">
        <?php wp_nonce_field( 'updating-awtoolkit' );
        /* Used to save closed meta boxes and their order */
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
 
        <!-- Rest of admin page here -->
        <div id="post-body" class="metabox-holder">
            <div class="awbox">
                <h4>Database find and replace <span style="color:orange;"> - Warning, can damage website if used incorrectly, backup site first</span></h4>
                <p>Find and replace all instances of a word in specific database tables, safely handling serialized data.</p>
                <div class="postbox-container" style="width:31%; padding: 0 1%;">
                    <label for="replace_table">Database Table</label>
                    <select name="replace_table" id="replace_table" style="width:100%;">
                        <?php
                            foreach($db_tables as $k=>$v){
                                $table = array_values($v)[0];
                                echo('<option name="'.$table.'" '.(isset($_POST['replace_table']) && $_POST['replace_table'] == $table ? 'selected="selected"' : '').'>'.$table.'</option>');
                            }
                        ?>
                    </select>
                </div>
                <div class="postbox-container" style="width:31%; padding: 0 1%;">
                    <label for="replace_find">Find Text</label>
                    <input type="text" name="replace_find" id="replace_find" style="width:100%;">
                </div>
                <div class="postbox-container" style="width:31%; padding: 0 1%;">
                    <label for="replace_replace">Replace Text</label>
                    <input type="text" name="replace_replace" id="replace_replace" style="width:100%;">
                </div>
                <br style="clear:both;">
                <p>
                    <input type="checkbox" name="replace_accept" id="replace_accept" value="1" >
                    <label for="replace_accept">I accept the risk of making database changes and have backed up my database.</label>
                </p>
                <?php if(isset($replace_msg) && $replace_msg){?><p style="font-weight:bold;"><?=$replace_msg?></div><?php } ?>
            </div>
            
            <div class="awbox">
                <h4>Woocommerce</h4>
                <p>
                    <input type="checkbox" name="aw_woo_prod_sales_count" id="aw_woo_prod_sales_count" <?= get_option('aw_woo_prod_sales_count') ?'checked="checked"':''?>>
                    <label for="aw_woo_prod_sales_count">Woocommerce product list will display total count of sales for each product</label>
                </p>
                <p>
                    <input type="checkbox" name="aw_woo_prod_dimensions" id="aw_woo_prod_dimensions" <?= get_option('aw_woo_prod_dimensions') ?'checked="checked"':''?>>
                    <label for="aw_woo_prod_dimensions">Woocommerce product list will display dimensions of each product</label>
                </p>
                <p>
                    <input type="checkbox" name="aw_woo_past_order_count" id="aw_woo_past_order_count" <?= get_option('aw_woo_past_order_count') ?'checked="checked"':''?>>
                    <label for="aw_woo_past_order_count">Woocommerce order list will display count of past orders for the customer</label>
                </p>
                <p>
                    <input type="checkbox" name="aw_woo_utm" id="aw_woo_utm" <?= get_option('aw_woo_utm') ?'checked="checked"':''?> >
                    <label for="aw_woo_utm">Woocommerce orders will track UTM parameters from inbound links</label>
                </p>
                <p>
                    <input type="checkbox" name="aw_woo_msp" id="aw_woo_msp" <?= get_option('aw_woo_msp') ?'checked="checked"':''?> >
                    <label for="aw_woo_msp">Manage suppliers per product</label>
                </p>
            </div>
            <div class="awbox">
                <h4>Maintenance / Coming soon mode</h4>
                <p>
                    <input type="checkbox" name="aw_maint_mode" id="aw_maint_mode" <?= get_option('aw_maint_mode') ?'checked="checked"':''?>>
                    <label for="aw_maint_mode">Hide your site content from non-admin users while work is in progress.</label>
                </p>
                <p>
                    <label for="aw_maint_title">Title</label>
                    <input type="text" name="aw_maint_title" id="aw_maint_title" value="<?= get_option('aw_maint_title') ? get_option('aw_maint_title'):'Website coming soon!'?>">
                </p>
                <label for="aw_maint_message">Content</label>
                <textarea name="aw_maint_message" style="width:100%;"><?= stripslashes(get_option('aw_maint_message'))?></textarea>
                <label for="aw_maint_exceptions">Exceptions (1 url per line)</label>
                <textarea name="aw_maint_exceptions" style="width:100%;"><?= stripslashes(get_option('aw_maint_exceptions'))?></textarea>
            </div>
            <div class="awbox">
                <h4>Cloudflare Flexible SSL</h4>
                <p>Prevent Cloudflare Flexible SSL from causing errors with a redirect loop.</p>
                <p>This works automatically if cloudflare is enabled for your site, no configuration necessary.</p>
                <p><a href="https://www.cloudflare.com" target="_blank">Cloudflare</a> can help speed up and secure your website as well as provide free SSL certificates.</p>
            </div>
            
            <br style="clear:both;">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"><br>
            <br>
            <hr>
            <a href="https://awcode.com" target="_blank"><h4>Developed by AWcode</h4></a>
            <p>This plugin is a collection of useful tools and enhancements to help get the most out of your wordpress site.</p>
            <p>
                For help, support or feature requests please <a href="https://awcode.com/contact/" target="_blank">contact us</a>.<br>
                Our team of experienced web developers can also help with building and customising your website.
            </p>
        </div>
	</form>
</div>
