<?php


/**
 *
 *	table: elements
 		el_id int primary key
		el_name string varchar(32)

	table: attributes
 		attr_id int primary key
		attr_name string varchar(32)

	table: attribute_values
		attr_v_id
		attr_v_name string varchar(32)
	
	table: element_types
		el_type_id
		el_type_name string varchar(32)

	table: dtd
		dtd_id
		dtd_name string varchar(64)

	table: element_close
		el_close_id
		el_close_name


	table: attributs_attribute_values
		attr_attr_val__attr_id
		attr_attr_val__attr_v_id
		attr_attr_val__dtd_id

	table: elements_attributes
		el_attr__el_id
		el_attr__attr_id
		el_attr__dtd_id
	
	table: elements_relations
		el_rel__el_id_primary integer
		el_rel__el_id_relation integer
		el_rel__child boolean
		el_el_type__dtd_id

	table: element_element_type
		el_el_type__el_id
		el_el_type__el_type_id
		el_el_type__dtd_id

	table: element_element_close
		el_close__el_id
		el_close__el_close_id
		el_close__dtd_id
 */





function grab_tag_and_children( $input , $element , $id = '' , $class = '' , $dtd = '' )
{
	
};
