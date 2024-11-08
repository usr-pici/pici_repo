
<div class="row">
	<div class="col-md-12">
		<div class="text-center page_title">
			<h3>
				<?php echo $config['title']; ?>
			</h3>
		</div> 
	</div>
</div>   

<div class="row">
	<div class="col-md-12">
		<div class="text-center" style="margin-top:5px;">
			<button type="button" id="btn_nuevo_reg" class="botonIconText" icon="plus">Nuevo registro</button>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
			<div class="col-md-12 table-content">
				<table id="tabla_regs" style="width:100% !important;">
					<thead>
						<tr> <?php
							foreach ( $config['headers'] as $index => $header ) {
									
                                                            if ( !empty( $config['columns'][$index]['notShowInTable'] ) ) {
                                                                
                                                                continue;
                                                            }
							?>
									<th><?php echo $header ? $header : 'Activo'; ?></th> <?php
								
							} ?>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
		</div>
	</div>
</div>

<div id="dialog-delete">
    <p>&iquest;Eliminar el registro seleccionado, con ID <strong id="text_id_reg"></strong>?</p>
	<p id="note_delete"><strong>Nota:</strong> se eliminar&aacute;n los subtipos de tarjeta que dependan de este tipo.</p>
</div>

<div id="dialog-registro">
	<div class="row">
        <div class="col-md-11">			
			<div class="text-right" style="margin-bottom:10px;">
				<strong>
                                    <label class="small text-danger">Los campos marcados con asterisco (*) son obligatorios.</label>
				</strong>
			</div>
		</div>
	</div>		
	<form id="form_reg" class="form-horizontal"> 
		<?php if ( $config['catalogo'] == 'sucmin' ) {
			echo '
			<div class="row form-group" id="div_identificador_input">
				<div class="col-md-4">
					<label class="font-weight-bold"><div class="text-right">ID:</div></label>
				</div>
				<div class="col-md-7">
					<input type="text" name="reg[ID_Sucursal]" id="c_r_ID_Sucursal" style="" placeholder=""  class="form-control f_alfanum ceros">
				</div>
			</div>';
		}
		else {
			echo '
			<div class="row form-group" id="div_identificador">
				<label class="col-md-4 control-label"><div class="text-right">ID:*</div></label>
				<div class="col-md-7"><div id="identificador" class="form-control-static"></div></div>	
			</div>';
		
		}
		?>
		<?php
		
		foreach ( $config['headers'] as $index => $header ) { 
		
			$campo = $config['columns'][$index]['data'];												
		
			if ( in_array( $campo, array($config['key_field'], 'opciones') ) ) { 
			
				continue;
			}
			
			$type = $config['columns'][$index]['type'];
			$style = $config['columns'][$index]['style'];
			$marca_req = $config['columns'][$index]['required'] ? ' <span class="text-danger">*</span>' : '';
			$format_class = $config['columns'][$index]['class'] ? $config['columns'][$index]['class'] : 'f_alfanum';

			if($campo == 'largo' || $campo == 'ancho' || $campo == 'alto')
				$format_class = 'maskInteger';

			
			if ( !empty($config['dependencia'][$campo]) ) { ?>                        
			
				<div class="row form-group">
					<label class="col-md-4 control-label"><div class="text-right"><?php echo $header; ?>:<?php echo $marca_req; ?></div></label>
					<div class="col-md-7">                            
						<select <?php echo empty($config['dependencia'][$campo]['campo_dependiente']) ? '' : ' data-dependencia="'.$config['dependencia'][$campo]['campo_dependiente'].'"'; ?> name="reg[<?php echo $campo ; ?>]" id="c_r_<?php echo $campo ; ?>" class="form-control"><?php echo $config['dependencia'][$campo]['regs_to_select']; ?></select>
					</div>                   
				</div> <?php
				
			} elseif ( !empty($config['config_field'][$campo]) ) { ?>
			
				<?php
					$label_radio = !empty( current(array_keys($config['config_field'])) ) ? current(array_keys($config['config_field'])) : '';
				?>
				<div class="row form-group">
					<label class="col-md-4 control-label">
						<div class="text-right">
							<?php echo ucfirst($label_radio); ?>:<?php echo $marca_req; ?>
						</div>
					</label>
					<div class="col-md-7"> <?php
						foreach ( $config['config_field'][$campo]['options'] as $opt ) { ?>							    
							<label class="radio-inline">        
								<input type="radio" name="reg[<?php echo $campo ; ?>]" value="<?php echo $opt['val']; ?>" class="field_options <?php echo empty($opt['default']) ? '' : 'selected_default';?>" /><?php echo $opt['desc']; ?> 
							</label> 
						   <?php
						} ?>
					</div>   
				   
				</div> <?php		
			} else { ?>
		<?php   if ( $config['catalogo'] == 'sucmin' &&   $header == 'Nueva') {?>
					<div class="row form-group" id="">
						<label class="col-md-4 control-label"><div class="text-right"><?php echo $header; ?>:<?php echo ' '. $marca_req; ?></div></label>
						<div class="col-md-7">
							<input type="text" name="reg[<?php echo $campo; ?>]" id="c_r_<?php echo $campo ; ?>" style="<?php echo $style; ?>" placeholder="" class="form-control <?php echo $format_class; ?>" disabled />	
						</div> 
					</div>
		<?php
				}else{?>
					<div class="row form-group">
						 
						<label class="col-md-4 control-label"><div class="text-right"><?php echo $header; ?>:<?php echo ' '. $marca_req; ?></div></label>
						<div class="col-md-7"><?php
							if ( $type === 'textarea' ) { ?>
								<textarea name="reg[<?php echo $campo; ?>]" id="c_r_<?php echo $campo; ?>" style="<?php echo $style; ?>" class="form-control <?php echo $format_class; ?>"></textarea> <?php
							} else { ?>                     
								<input <?php echo $campo == 'volumen' ? 'disabled' : '' ; ?> type="text" name="reg[<?php echo $campo; ?>]" id="c_r_<?php echo $campo ; ?>" style="<?php echo $style; ?>" placeholder="" class="form-control <?php echo $format_class; ?>" /> <?php
							} ?>									
						</div>                    
					</div> 
					
		<?php	}
			}
		} ?>
		 
	</form>
	<div class="row">
		<div class="col-md-10 col-md-offset-1 error">
			<div id="form-box-msg" style="display:none;"></div>  
		</div>
    </div>
</div>


<script>
    
    document.onreadystatechange = function () {
  var state = document.readyState;
//  console.log(state);
  /*if (state == 'interactive') {
      init();
  } else*/ if (state == 'complete') {
      $config = <?php echo json_encode($config); ?>;
		//alert($config);
		config_datatable();
  }
};

</script>
