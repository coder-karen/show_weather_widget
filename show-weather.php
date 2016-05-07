<?php

/*
Plugin Name: Show Weather
Plugin URI: http://k1demo.byethost6.com
Description: A Widget for displaying the current weather using the Yahoo API current
Version: 1.0
Author: Karen Attfield
Author URI: karenatt.carbonmade.com
License: GPL V2 (or later)
*/



class Show_Weather_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'show_weather_widget',
			'Show Weather Widget',
			array( 'description' => 'A Widget for displaying the current weather using the Yahoo API' ) 
		);
	}



public function widget( $args, $instance ) {

	extract( $args );
	$title = apply_filters( 'widget_title', $instance['title'] );
	$location = $instance['location'];

	wp_register_style('show-weather-style', plugins_url('show-weather/show-weather.css', dirname(__FILE__)));
	wp_enqueue_style('show-weather-style');
		 
	$json_feed_url = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20weather.forecast%20where%20woeid%20in%20(select%20woeid%20from%20geo.places(1)%20where%20text%3D%22" . $location . "%22)%20and%20u%3D%27c%27&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";


	$response = wp_remote_get( $json_feed_url );


	$show_weather = json_decode($response['body']);


	echo $before_widget;
	?>

	<div class='show-weather-widget'>
		<?php if(isset($title)){ ?>
		<h3 class='widget-title'><?php echo $title ?></h3>		
		<?php 
		}

		$conditionCode = $show_weather->query->results->channel->item->condition->code;
		$showConditionImage = "http://l.yimg.com/a/i/us/we/52/" . $conditionCode . ".gif";

	    $currentTemp = $show_weather->query->results->channel->item->condition->temp;
    	$currentConditions = $show_weather->query->results->channel->item->condition->text;

		$wind = $show_weather->query->results->channel->wind->speed;

     	$windDirection = $show_weather->query->results->channel->wind->direction;

		$compass = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW', 'N');

		$showWindDirection = $compass[round($windDirection / 22.5)];

 		$humidity = $show_weather->query->results->channel->atmosphere->humidity;


		?>
			<div>			
				<div class="temp"><p><?php echo $currentTemp ?>&deg;C</p><p><?php echo $currentConditions ?></p></div>
				
				<div class="image"><img src="<?php echo $showConditionImage ?>" alt="Current weather conditions image"/></div>
				
				<div class="wind"><strong>Wind: </strong><?php echo $wind ?> km/h <?php echo $showWindDirection ?></div>
				<div class="humidity"><strong>Humidity: </strong><?php echo $humidity ?>%</div>
		
			</div>
	</div>

	<?php	
		echo $after_widget;
}



public function update( $new_instance, $old_instance ) {
	$instance = array();
	$instance['title'] = strip_tags( $new_instance['title'] );
	$instance['location'] = strip_tags( $new_instance['location'] );
	return $instance;
	}

public function form( $instance ) {
	if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
	}
	else {
		$title = 'New title';
	}
	
	
	if ( isset( $instance[ 'location' ] ) ) {
		$location = $instance[ 'location' ];
	}
	else {
		$location = '';
	}
	?>
	<p>
	<label for="<?php echo $this->get_field_id( 'location' ); ?>"><?php _e( 'Location:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>" type="text" value="<?php echo esc_attr( $location ); ?>" />

	<p style="font-size:smaller;font-style:italic">Type in name of location, or find location with WOEID (Yahoo weather ID) <a href="https://www.yahoo.com/news/weather/">here</a> and copy name-ID format as shown at end of url.</p>
	
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	

	</p>


	<?php 
	}

}

function show_weather_widgets_init(){
	register_widget( 'Show_Weather_Widget' );
}

add_action( 'widgets_init', 'show_weather_widgets_init' );

?>