<?php
/*
  Plugin Name: Featured Book Widget
  Plugin URI: http://alexiv.es
  Description: A widget for featuring books in a widget. Takes an isbn and pulls the cover and other info from google.
  Author: Alex Ives
  Version: 1.1
  Author URI: http://alexiv.es
*/

class FeaturedBookWidget extends WP_Widget {
	
	/*
	 * FeaturedBookWidget is the constructor for the new widget.
	 */
	function FeaturedBookWidget() {
		$widget_ops = array (
				'classname' => 'FeaturedBookWidget',
				'description' => 'Displays the cover and Author of a featured book.' 
		);
		// Uses the existing WP_Widget constructor
		$this->WP_Widget ( 'FeaturedBookWidget', 'Featured Book', $widget_ops );
	}
	
	/*
	 * form deals with the form used on the WP back end in order to set
	 * widget preferences.
	 */
	function form($instance) {
		$instance = wp_parse_args ( ( array ) $instance, array (
				'title' => '',
				'isbn' => '',
				'link' => '',
				'author' => '',
				'bookTitle' => '',
				'image' => '',
				'gbLink' => '',
				'subTitle' => ''
		) );
		$title = $instance ['title'];
		$isbn = $instance ['isbn'];
		$link = $instance ['link'];
		$bookTitle = $instance['bookTitle'];
		// Outputs the form HTML
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>">Widget Title:
		<input class="widefat"
		id="<?php echo $this->get_field_id('title'); ?>"
		name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo attribute_escape($title); ?>" />
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('isbn'); ?>">ISBN: <input
		class="widefat" id="<?php echo $this->get_field_id('isbn'); ?>"
		name="<?php echo $this->get_field_name('isbn'); ?>" type="text"
		value="<?php echo attribute_escape($isbn); ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('link'); ?>">Links to: <input
		class="widefat" id="<?php echo $this->get_field_id('link'); ?>"
		name="<?php echo $this->get_field_name('link'); ?>" type="text"
		value="<?php echo attribute_escape($link); ?>" /></label>
</p>
<p>
	Book Title: <?php echo $bookTitle; ?>
</p>

<?php
	}
	
	/*
	 * update is used when the user saves new settings in the
	 * widget preferences. Also, pulls data from google and adds
	 * it to the database. This means that it won't have to pull 
	 * data every time someone loads a page, just an image from 
	 * the client side. 
	 */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance ['title'] = $new_instance ['title'];
		$instance ['isbn'] = $new_instance ['isbn'];
		$instance ['link'] = $new_instance ['link'];
		// Gets information about the book from the google books api based on isbn.
		$data = json_decode ( file_get_contents ( 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $instance ['isbn'], 0, null, null ) );
		$instance['bookTitle'] = $data->items [0]->volumeInfo->title;
		$instance['subTitle'] = $data->items [0]->volumeInfo->subtitle;
		$instance['image'] = $data->items [0]->volumeInfo->imageLinks->thumbnail;
		$instance['gbLink'] = $data->items [0]->volumeInfo->infoLink;
		// makes a string list of authors in case there are many.
		$authors = $data->items [0]->volumeInfo->authors;
		$instance['author'] = $authors [0];
		for($i = 1; $i < count ( $authors ); $i ++) {
			$instance['author'] += " and " . $authors [$i];
		}
		return $instance;
	}
	
	/*
	 * handles calling the widget in the front end.
	 */
	function widget($args, $instance) {
		extract ( $args, EXTR_SKIP );
		
		$title = empty ( $instance ['title'] ) ? ' ' : apply_filters ( 'widget_title', $instance ['title'] );
		$isbn = $instance ['isbn'];
		$link = $instance ['link'];
		$gbLink = $instance['gbLink'];
		$thumbnail = $instance['image'];
		$bookTitle = $instance['bookTitle'];
		$author = $instance['author'];
		$subTitle = empty( $instance['subTitle']) ? '' : ": ".$instance['subTitle'];
		// if the link is empty, use the link to the google books page.
		if (empty ( $link )) {
			$link = $gbLink;
		}

		echo $before_widget;
		// if the title is empty, don't display it.
		if (! empty ( $title ))
			echo $before_title . $title . $after_title;
		echo ("<a href='$link'><span class='fbw-thumbnail'><img src='$thumbnail' /></span><br /><span style='font-size: 1.3em' class='fbw-title'>$bookTitle</span><span style='font-size: 1.1em' class='fbw-subTitle'>$subTitle</span><br /><span class='fbw-author'>by $author</span></a>");
		echo ("<br /><a style='font-size: .65em' href='$gbLink' target='_BLANK'>Powered by Google</a>");
		echo $after_widget;
	}
}
// Registers the function with wordpress.
add_action ( 'widgets_init', create_function ( '', 'return register_widget("FeaturedBookWidget");') );

?>