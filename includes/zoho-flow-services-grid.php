<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Zoho_Flow_Services_Grid extends Zoho_Flow_Services{
  private $services;

  public function __construct(){
    $this->services = Zoho_Flow_Services::get_instance()->get_services();
  }

  public function column_name($item){

    if(!get_option('permalink_structure') || !$item['is_available']){
      return '<b class="app-title">'.esc_html( $item['name'] ).'</b>';
    }
    else{

      $edit_link = add_query_arg(
        array(
          'service' => $item['id']
        ),
        menu_page_url( 'zoho_flow', false )
      );

      $output = sprintf(
        '<a class="app-title" href="%1$s" aria-label="%2$s">%3$s</a>',
        esc_url( $edit_link),
        // translators: %s refers to the plugin name
        esc_attr( sprintf( __( 'Edit %s', 'zoho-flow' ),
          $item['name'] ) ),
        esc_html( $item['name'] )
      );

      $output = sprintf( '<strong>%s</strong>', $output );

      return $output;
    }

  }

	public function app_link_name($item){

		if(!get_option('permalink_structure') || !$item['is_available']){
			return esc_attr("#TB_inline?width=500&height=150&inlineId=service_details_popup_".$item['id']);
		}
		else{

			$edit_link = add_query_arg(
				array(
					'service' => $item['id']
				),
				menu_page_url( 'zoho_flow', false )
			);

			return $edit_link;
		}

	}

  function column_icon_file($item){
		$file = $item['icon_file'];
		if(!file_exists(__DIR__ . '/../assets/images/logos/' . $file)){
			return '<img>';
		}
		return "<img src='" . esc_attr(esc_url(plugins_url('../assets/images/logos/' . $file, __FILE__))) . "' alt='". $item['id'] ."' style='height:64px'>";
	}

  public function display(){
    ?>
    <div class="app-list-container" style="display:grid; grid-template-columns:repeat(4, 1fr); padding-bottom: 0px;" >
      <?php
        foreach ($this->services as $service) {
					if(get_option('permalink_structure') && $service['is_available']){
						?>

			      	<div id='<?php echo $service['id'] ?>' class="grid-app-wrapper grid-app-available">
								<a href='<?php echo $this->app_link_name($service) ?>'>
									<div class="grid-app-icon">
			              <center>
			                <?php echo $this->column_icon_file($service) ?>
			              </center>
			            </div>
			            <div class="grid-app-name">
			              <center>
			                <?php echo $this->column_name($service) ?>
			              </center>
			            </div>
									</a>
			          </div>
						<?php
			    }
        }
				?>
			</div>
			<div class="app-list-container" style="display:grid; grid-template-columns:repeat(4, 1fr); padding-top: 0px;" >
				<?php
				foreach ($this->services as $service) {
					if(get_option('permalink_structure') && !$service['is_available']){
						?>
						<div id="service_details_popup_<?php echo $service['id'] ?>" style="display:none;">
							<div class="service-details-popup" style="width:550px;text-align: center;">
								<center>
									<div class="service-details-popup-app-icon">
										<center>
											<?php echo $this->column_icon_file($service) ?>
										</center>
									</div>
									<div class="service-details-popup-app-name">
										<center>
											<strong>
												<?php echo $service['name'] ?>
											</strong>
										</center>
									</div>
									<div class="service-details-popup-app-description">
										<center>
												<?php echo $service['description'] ?>
										</center>
									</div>
								</center>
							</div>
							<div class="service-details-popup-app-not-available-banner">
								<center>
										<?php echo 'Plugin not Installed / Activated' ?>
								</center>
							</div>
						</div>
							<div id='<?php echo $service['id'] ?>' class="grid-app-wrapper grid-app-not-available">
								<a href='<?php echo $this->app_link_name($service) ?>' class='thickbox'>
									<div class="grid-app-icon">
			              <center>
			                <?php echo $this->column_icon_file($service) ?>
			              </center>
			            </div>
			            <div class="grid-app-name">
			              <center>
			                <?php echo $this->column_name($service) ?>
			              </center>
			            </div>
									</a>
			          </div>
						<?php
			    }
        }
      ?>
			<div id='app-request' class="grid-app-wrapper grid-app-request">
				<a href="https://creatorapp.zohopublic.com/zohointranet/zoho-flow/form-embed/Request_an_App/qqePxZq7ZkzdWKGCYvntEk14O9YqjUGHJUZJHYsMA5zOK6XEC8b6Gh7mrdz2TnYu4AUVBRwu1YzKVU8KAwbn2OurBsJ66FqkT8Rm?zc_BdrClr=ffffff&zc_Header=false&TB_iframe=true&width=320&height=440" class="thickbox" title="New integration request">
					<div class="grid-app-request-icon">
						<div class="plus alt"> </div>
					</div>
					<div style="font-size: 15px; color:#505254;" class="grid-app-name">
						<center>
							<strong>
								<?php echo 'Request new integration' ?>
							</strong>
						</center>
					</div>
					</a>
				</div>
    	</div>

    <?php
  }
}
