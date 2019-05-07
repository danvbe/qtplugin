<?php
/**
 * Created by PhpStorm.
 * User: ubuntu
 * Date: 5/7/19
 * Time: 12:37 PM
 */

class QTPlugin_Forms {

	public static function listQuotes(){
		$quotes =QTPlugin_API::getQuotes();
		?>
        <div class="wrap">
            <h3><?php _e('Quotes list', 'qtplugin'); ?></h3>

            <hr>

            <table class="form-table">
                <thead>
                <tr>
                    <th>
                        Author
                    </th>
                    <th>
                        Quote
                    </th>
                    <th>
                        Actions
                    </th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($quotes as $quote):?>
                    <tr>
                        <td>
							<?php echo $quote['author']; ?>
                        </td>
                        <td>
							<?php echo $quote['text']; ?>
                        </td>
                        <td>
                            <a class="button button-primary" href="<?php echo QTPlugin::getURL().'&qtp_page=show&qtp_id='.$quote['id']?>">Show</a>
                            <a class="button button-primary" href="<?php echo QTPlugin::getURL().'&qtp_page=edit&qtp_id='.$quote['id']?>">Edit</a>
                            <a class="button button-secondary" href="<?php echo QTPlugin::getURL().'&qtp_page=delete&qtp_id='.$quote['id']?>">Delete</a>
                        </td>
                    </tr>
				<?php endforeach;?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2">
                        <a class="button button-primary" href="<?php echo QTPlugin::getURL().'&qtp_page=new'?>"><?php _e( 'Add', 'qtplugin' ); ?></a>
                    </td>
                </tr>
                </tfoot>
            </table>

        </div>

		<?php
	}

	public static function newQuote($data = array()){
		?>
        <div class="wrap">
            <h3><?php _e('Add new quote', 'qtplugin'); ?></h3>
			<?php if(isset($data['author'])):?>
                <h4 class="error"><?php _e('There was an error processing the data. Please try again!', 'qtplugin'); ?></h4>
			<?php endif?>
            <hr>

            <form id='qtp_new' method="post">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <td scope="row">Author</td>
                        <td>
                            <input name="author"
                                   id="author"
                                   class="regular-text"
                                   value="<?php isset($data['author'])?$data['author']:'' ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">Quote</td>
                        <td>
							<textarea name="text"
                                      id="text"
                                      value="<?php isset($data['text'])?$data['text']:'' ?>">
							</textarea>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2">
                            <button class="button button-primary" type="submit"><?php _e( 'Add', 'qtplugin' ); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <a class="button button-secondary" href="<?php echo QTPlugin::getURL().'&qtp_page=list'?>"><?php _e( 'Back to List', 'qtplugin' ); ?></a>
                        </td>
                    </tr>
                    </tfoot>
                </table>

            </form>

        </div>

		<?php
	}

	public static function editQuote($id){
		$quote =QTPlugin_API::getQuote($id);
		?>
        <div class="wrap">
            <h3><?php _e('Edit quote', 'qtplugin'); ?></h3>
            <hr>

            <form id='qtp_new' method="post">
                <table class="form-table">
                    <tbody>
                    <input type="hidden" name="id" id="id" value="<?php echo $quote['id']?>">
                    <tr>
                        <td scope="row">Author</td>
                        <td>
                            <input name="author"
                                   id="author"
                                   class="regular-text"
                                   value="<?php echo $quote['author'] ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">Quote</td>
                        <td>
							<textarea name="text" id="text">
                                <?php echo $quote['text'] ?>
							</textarea>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2">
                            <button class="button button-primary" type="submit"><?php _e( 'Save', 'qtplugin' ); ?></button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <a class="button button-secondary" href="<?php echo QTPlugin::getURL().'&qtp_page=list'?>"><?php _e( 'Back to List', 'qtplugin' ); ?></a>
                        </td>
                    </tr>
                    </tfoot>
                </table>

            </form>

        </div>

		<?php
	}

	public static function showQuote($id){
		$quote =QTPlugin_API::getQuote($id);
		?>
        <div class="wrap">
            <h3><?php _e('Show quote', 'qtplugin'); ?></h3>
            <hr>

            <table class="form-table">
                <tbody>
                <tr>
                    <td scope="row">Author</td>
                    <td>
						<?php echo $quote['author']?>
                    </td>
                </tr>
                <tr>
                    <td scope="row">Text</td>
                    <td>
						<?php echo $quote['text']?>
                    </td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2">
                        <a class="button button-secondary" href="<?php echo QTPlugin::getURL().'&qtp_page=list'?>"><?php _e( 'Back to List', 'qtplugin' ); ?></a>
                    </td>
                </tr>
                </tfoot>
            </table>

        </div>

		<?php
	}

}