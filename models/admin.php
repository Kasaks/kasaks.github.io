<?php
// модель отвечает за запрос данных с базы данных и возврат их в контроллер ДЛЯ ПАНЕЛИ АДМИНИСТРАТОРА

include_once($_SERVER['DOCUMENT_ROOT'].'/components/db.php'); 

class Admin {

	// функция вытаскивает регистрационные данные из базы
	public static function get_autch ($login, $password) {

		$pdo = Db::get_connection();

		$autch = $pdo->query("SELECT login, password FROM users WHERE login = '$login' AND password = '$password'");
		
		$autch = $autch->fetch();
		
		if (is_array($autch)) { // защита от пустого массива данных
			return true;
		} else return false;
	}

	public static function rename ($login,  $new_password) {

		$pdo = Db::get_connection();

		$rename = $pdo->query("UPDATE users SET password = '$new_password' WHERE login = '$login'");

		$rename = $rename->fetch();

		if (isset($rename)) { // защита от пустого массива данных
			return true;
		} else return false;

	}

	public static function get_directory () {

		$pdo = Db::get_connection();

		$brand_data_array = $pdo->query('SELECT brand_id, brand FROM brands');
		$model_data_array = $pdo->query('SELECT model_id, model FROM models');
		$category_data_array = $pdo->query('SELECT part_category_id, part_category FROM parts_categories');
		$availability_data_array = $pdo->query('SELECT availability_id, availability FROM availability');
		$condition_data_array = $pdo->query('SELECT part_condition_id, part_condition FROM parts_conditions');

		// ЭЛЕМЕНТЫ МАССИВА НАЗВАНЫ ПО ЕБАНОМУ ЧТОБЫ БЫЛО УДОБНО ПИХАТЬ ДАННЫЕ В ПОЛЯ СПРАВОЧНИКОВ 

		$i = 0;
		while ($row = $brand_data_array->fetch()) {
			$directory_data_array['brands'][$i]['brands_id'] = $row['brand_id'];
			$directory_data_array['brands'][$i]['brands_text'] = $row['brand'];
			$i++;
		};

		$i = 0;
		while ($row = $model_data_array->fetch()) {
			$directory_data_array['models'][$i]['models_id'] = $row['model_id'];
			$directory_data_array['models'][$i]['models_text'] = $row['model'];
			$i++;
		};

		$i = 0;
		while ($row = $category_data_array->fetch()) {
			$directory_data_array['categories'][$i]['categories_id'] = $row['part_category_id'];
			$directory_data_array['categories'][$i]['categories_text'] = $row['part_category'];
			$i++;
		};

		$i = 0;
		while ($row = $availability_data_array->fetch()) {
			$directory_data_array['availability'][$i]['availability_id'] = $row['availability_id'];
			$directory_data_array['availability'][$i]['availability_text'] = $row['availability'];
			$i++;
		};

		$i = 0;
		while ($row = $condition_data_array->fetch()) {
			$directory_data_array['conditions'][$i]['conditions_id'] = $row['part_condition_id'];
			$directory_data_array['conditions'][$i]['conditions_text'] = $row['part_condition'];
			$i++;
		};

		if(isset($directory_data_array)) {
			return $directory_data_array;
		} else return false;

	}

	public static function insert_post_data ($title, $brands, $models, $images_flags, $category, $condition, $availability, $cost, $discription, $status, $part_number) {

		$pdo = Db::get_connection();

		$query['parts'] = $pdo->prepare("
			INSERT INTO parts (part_title, part_cost, part_number, part_discription, part_condition_id, part_category_id,part_status_id, part_availability_id) VALUES (:title, :cost, :part_number, :discription, :condition, :category, :status, :availability)
		");
		$query['parts']->execute(
			array(
				'title' => $title,
				'cost' => $cost,
				'part_number' => $part_number,
				'discription' => $discription,
				'condition' => $condition,
				'category' => $category,
				'status' => $status,
				'availability' => $availability
			)
		);

		$post_id = $pdo->lastInsertId();

		foreach ($brands as $brand) {
			
			$query['parts_brands'] = $pdo->prepare("
				INSERT INTO con_parts_brands (parts_brands_part_id, parts_brands_brand_id) 
				VALUES (:part_id, :brand)
			");

			$query['parts_brands']->execute(
				array(
					'part_id' => $post_id,
					'brand' => $brand
				)
			);

		}

		foreach ($models as $model) {
			
			$query['parts_models'] = $pdo->prepare("
				INSERT INTO con_parts_models (parts_models_part_id, parts_models_model_id) 
				VALUES (:part_id, :model)
			");

			$query['parts_models']->execute(
				array(
					'part_id' => $post_id,
					'model' => $model
				)
			);

		}

		for($i = 0; $i < count($images_flags); $i++) {

			$image_name = $post_id . '_' . $i;

			$query['images'] = $pdo->prepare("INSERT INTO images (image, image_post_id) VALUES (:image, :post_id)");

			$query['images']->execute(array(
				'image' => $image_name,
				'post_id' => $post_id
			));

			$image_id_array[$i] = $pdo->lastInsertId();

		}

		for($i = 0; $i < count($image_id_array); $i++) {

			$query['parts_images'] = $pdo->prepare("INSERT INTO 
				con_parts_images (parts_images_part_id, parts_images_image_id, parts_images_main_image_flag) 
				VALUES 
				(:post_id, :image_id, :image_flag)");

			$query['parts_images']->execute(array(
				'post_id' => $post_id,
				'image_id' => $image_id_array[$i],
				'image_flag' => $images_flags[$i]
			));

		}
		
		return true;

	}

	public static function get_last_id () {

		$pdo = Db::get_connection();

		$last_id_pdo = $pdo->query('SELECT max(part_id) FROM parts');
		
		$i = 0;
		while ($row = $last_id_pdo->fetch()) {
			$last_id['part_id'] = $row['max(part_id)'];
			$i++;	
		};

		return $last_id['part_id'];

	}

	public static function get_brands_data() {

		$pdo = Db::get_connection();

		$brands_data_pdo = $pdo->query("
			SELECT brand_id, brand
			FROM brands
		");

		$i = 0;
		while ($row = $brands_data_pdo->fetch()) {
			$brands_data_array[$i]['id'] = $row['brand_id'];
			$brands_data_array[$i]['value'] = $row['brand'];

			$i++;
		}

		if(isset($brands_data_array)) {
			return $brands_data_array;
		} else return false;

	}

	public static function get_models_data($brand_id) {

		$pdo = Db::get_connection();

		$models_data_pdo = $pdo->query("
			SELECT model_id, model
			FROM models
			INNER JOIN con_brands_models ON models.model_id = con_brands_models.brands_models_model_id
			WHERE con_brands_models.brands_models_brand_id = '$brand_id'
		");

		$i = 0;
		while ($row = $models_data_pdo->fetch()) {
			$models_data_array[$i]['id'] = $row['model_id'];
			$models_data_array[$i]['value'] = $row['model'];

			$i++;
		}

		if(isset($models_data_array)) {
			return $models_data_array;
		} else return false;

	}

	public static function get_categories_data() {

		$pdo = Db::get_connection();

		$categories_data_pdo = $pdo->query("
			SELECT part_category_id, part_category
			FROM parts_categories
		");

		$i = 0;
		while ($row = $categories_data_pdo->fetch()) {
			$category_data_array[$i]['id'] = $row['part_category_id'];
			$category_data_array[$i]['value'] = $row['part_category'];

			$i++;
		}

		if(isset($category_data_array)) {
			return $category_data_array;
		} else return false;

	}

	public static function get_conditions_data () {

		$pdo = Db::get_connection();

		$conditions_data_pdo = $pdo->query("
			SELECT part_condition_id, part_condition
			FROM parts_conditions
		");

		$i = 0;
		while ($row = $conditions_data_pdo->fetch()) {
			$conditions_data_array[$i]['id'] = $row['part_condition_id'];
			$conditions_data_array[$i]['value'] = $row['part_condition'];

			$i++;
		}

		if(isset($conditions_data_array)) {
			return $conditions_data_array;
		} else return false;

	}

	public static function get_availability_data () {

		$pdo = Db::get_connection();

		$availability_data_pdo = $pdo->query("
			SELECT availability_id, availability
			FROM availability
		");

		$i = 0;
		while ($row = $availability_data_pdo->fetch()) {
			$availability_data_array[$i]['id'] = $row['availability_id'];
			$availability_data_array[$i]['value'] = $row['availability'];

			$i++;
		}

		if(isset($availability_data_array)) {
			return $availability_data_array;
		} else return false;

	}

	public static function get_table_fields ($table_name) {

		$pdo = Db::get_connection();

		$table_fields_pdo = $pdo->prepare("DESCRIBE $table_name");
		$table_fields_pdo->execute();
		$table_fields_array = $table_fields_pdo->fetchAll(PDO::FETCH_COLUMN);

		if(isset($table_fields_array)) {
			return $table_fields_array;
		} else return false;

	}

	public static function change_element($table_name, $field_id, $field_value, $value, $id) {

		$pdo = Db::get_connection();

		$query_data = [
			'value' =>  $value,
			'id' => $id
		];

		$sql = "UPDATE ".$table_name." SET ".$field_value."=:value WHERE ".$field_id."=:id";
		$queryResult = $pdo->prepare($sql);
		$queryResult->execute($query_data);

		return $queryResult;

	}

	public static function deleteElement($table_name, $id, $field_id) {

		$pdo = Db::get_connection();

		$query_data = [
			'id' => $id
		];

		$sql = "DELETE FROM ".$table_name." WHERE ".$field_id."=:id";
		$queryResult = $pdo->prepare($sql);
		$queryResult = $queryResult->execute($query_data);

		return $queryResult;

	}

	public static function check_record($table_name_check, $field_name) {

		$pdo = Db::get_connection();

		$sql = "SELECT ".$field_name." FROM ".$table_name_check." WHERE ".$field_name." = ".$id." LIMIT 1";
		$result = $pdo->query($sql);

		$result = $result->rowCount();

		return $result;
	}

	public static function insert_dir_element ($table_name, $id_field, $value_field, $value) {

		$pdo = Db::get_connection();

		$query_data = [
			'value' => $value
		];

		$sql = 'INSERT INTO '.$table_name.' ('.$value_field.') VALUES (:value)';

		$queryResult = $pdo->prepare($sql);
		$queryResult->execute($query_data);
		$id = $pdo-> lastInsertId();

		return $id;

	}

	public static function insert_con_element($id_brand, $id_model) {

		$pdo = Db::get_connection();

		$query_data = [
			'id_brand' => $id_brand,
			'id_model' => $id_model
		];

		$sql = "INSERT INTO con_brands_models (brands_models_brand_id, brands_models_model_id) VALUES (:id_brand,:id_model)";

		$queryResult = $pdo->prepare($sql);
		$queryResult->execute($query_data);

		return true;

	}

	public static function get_brand_id($brand) {

		$pdo = Db::get_connection();

		$sql = "SELECT brand_id FROM brands WHERE brand = '" . $brand . "'";

		$brand_id_pdo = $pdo->query($sql);

		$brand_id = $brand_id_pdo->fetch();

		return $brand_id['brand_id'];

	}

};