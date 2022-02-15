<?php
	// ФАЙл ОТВЕЧАЕТ ЗА ЗАПРОС ДАННЫХ ИЗ БД ДЛЯ КАТАЛОГА
	include_once($_SERVER['DOCUMENT_ROOT'].'/components/db.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/components/functions.php');

	class Catalog {

		public static function get_more_catalog_elements($last_id) {

			$pdo = Db::get_connection();

			$catalog_pdo = $pdo->query("
				SELECT part_id, part_title, part_number, part_cost, part_discription, availability.availability, parts_conditions.part_condition, images.image, parts_categories.part_category
				FROM parts
				INNER JOIN parts_conditions On parts.part_condition_id = parts_conditions.part_condition_id
				INNER JOIN availability ON parts.part_availability_id = availability.availability_id
				INNER JOIN parts_categories ON parts.part_category_id = parts_categories.part_category_id
				INNER JOIN con_parts_images ON parts.part_id = con_parts_images.parts_images_part_id
				INNER JOIN images ON con_parts_images.parts_images_image_id = images.image_id
				WHERE part_status_id = 2 AND con_parts_images.parts_images_main_image_flag = 1 AND part_id < $last_id
				ORDER BY part_id DESC LIMIT 10
			");

			$i = 0;
			while ($row = $catalog_pdo->fetch()) {
				$catalog_array[$i]['part_id'] = $row['part_id'];
				$catalog_array[$i]['part_title'] = $row['part_title'];
				$catalog_array[$i]['part_title_translit'] = Functions::translit_text($row['part_title']);
				$catalog_array[$i]['part_number'] = $row['part_number'];
				$catalog_array[$i]['part_cost'] = $row['part_cost'];
				$catalog_array[$i]['part_discription'] = $row['part_discription'];
				$catalog_array[$i]['part_availability'] = $row['availability'];
				$catalog_array[$i]['part_condition'] = $row['part_condition'];
				$catalog_array[$i]['part_category'] = $row['part_category'];
				$catalog_array[$i]['part_image'] = $row['image'];
				$i++;
			}

			// проверка сработает в слуачае если запрос выше не получил данных
			if(!isset($catalog_array)) {
				return false;
			};

			for ($i = 0; $i < count($catalog_array); $i++) {

				$part_id = $catalog_array[$i]['part_id'];

				$brands_pdo = $pdo -> query("
					SELECT brands.brand
					FROM parts
					INNER JOIN con_parts_brands ON parts.part_id = con_parts_brands.parts_brands_part_id
					INNER JOIN brands ON con_parts_brands.parts_brands_brand_id = brands.brand_id
					WHERE parts.part_id = $part_id
				");

				$models_pdo = $pdo->query("
					SELECT models.model
					FROM parts
					INNER JOIN con_parts_models ON parts.part_id = con_parts_models.parts_models_part_id
					INNER JOIN models ON con_parts_models.parts_models_model_id = models.model_id
					WHERE parts.part_id = $part_id
				");

				$j = 0;
				while ($row = $models_pdo->fetch()) {
					$catalog_array[$i]['part_model'][$j] = $row['model'];
					$j++; 
				}

				$j = 0;
				while ($row = $brands_pdo->fetch()) {
					$catalog_array[$i]['part_brand'][$j] = $row['brand'];
					$j++; 
				}

			}

			if(is_array($catalog_array)) {
				return $catalog_array;
			} else return false;
			
		}

		public static function get_catalog () { // получение данных как при загрузке страниц "Главная" и "Каталог", так и при запросе данных для фильтра
			$pdo = Db::get_connection();
			
			$check_empty = $pdo->query('select count(*) from parts')->fetchColumn();

			if ($check_empty == 0) { // проверка имеются ли записи в БД

				return false;

			};

			$catalog_pdo = $pdo->query("
				SELECT part_id, part_title, part_number, part_cost, part_discription, availability.availability, parts_conditions.part_condition, images.image, parts_categories.part_category
				FROM parts
				INNER JOIN parts_conditions ON parts.part_condition_id = parts_conditions.part_condition_id
				INNER JOIN availability ON parts.part_availability_id = availability.availability_id
				INNER JOIN parts_categories ON parts.part_category_id = parts_categories.part_category_id
				INNER JOIN con_parts_images ON parts.part_id = con_parts_images.parts_images_part_id
				INNER JOIN images ON con_parts_images.parts_images_image_id = images.image_id
				WHERE part_status_id = 2 AND con_parts_images.parts_images_main_image_flag = 1
				ORDER BY part_id DESC
			");

			$i = 0;
			while ($row = $catalog_pdo->fetch()) {
				$catalog_array[$i]['part_id'] = $row['part_id'];
				$catalog_array[$i]['part_title'] = $row['part_title'];
				$catalog_array[$i]['part_title_translit'] = Functions::translit_text($row['part_title']);
				$catalog_array[$i]['part_number'] = $row['part_number'];
				$catalog_array[$i]['part_cost'] = $row['part_cost'];
				$catalog_array[$i]['part_discription'] = $row['part_discription'];
				$catalog_array[$i]['part_availability'] = $row['availability'];
				$catalog_array[$i]['part_condition'] = $row['part_condition'];
				$catalog_array[$i]['part_category'] = $row['part_category'];
				$catalog_array[$i]['part_image'] = $row['image'];
				$i++;
			}

			for ($i = 0; $i < count($catalog_array); $i++) {

				$part_id = $catalog_array[$i]['part_id'];

				$brands_pdo = $pdo -> query("
					SELECT brands.brand
					FROM parts
					INNER JOIN con_parts_brands ON parts.part_id = con_parts_brands.parts_brands_part_id
					INNER JOIN brands ON con_parts_brands.parts_brands_brand_id = brands.brand_id
					WHERE parts.part_id = $part_id
				");

				$models_pdo = $pdo->query("
					SELECT models.model
					FROM parts
					INNER JOIN con_parts_models ON parts.part_id = con_parts_models.parts_models_part_id
					INNER JOIN models ON con_parts_models.parts_models_model_id = models.model_id
					WHERE parts.part_id = $part_id					
				");

				$j = 0;
				while ($row = $models_pdo->fetch()) {
					$catalog_array[$i]['part_model'][$j] = $row['model'];
					$j++; 
				}

				$j = 0;
				while ($row = $brands_pdo->fetch()) {
					$catalog_array[$i]['part_brand'][$j] = $row['brand'];
					$j++; 
				}

			}

			if(is_array($catalog_array)) {
				return $catalog_array;
			} else return false;

		}

		public static function get_filter_data () {

			$pdo = Db::get_connection();

			$brand_data_array = $pdo->query('SELECT brand_id, brand FROM brands');
			$category_data_array = $pdo->query('SELECT part_category_id, part_category FROM parts_categories');
			$availability_data_array = $pdo->query('SELECT availability_id, availability FROM availability');
			$condition_data_array = $pdo->query('SELECT part_condition_id, part_condition FROM parts_conditions');

			$i = 0;
			while ($row = $brand_data_array->fetch()) {
				$filter_data_array['brands'][$i]['brand_id'] = $row['brand_id'];
				$filter_data_array['brands'][$i]['brand'] = $row['brand'];
				$i++;
			};

			$i = 0;
			while ($row = $category_data_array->fetch()) {
				$filter_data_array['categories'][$i]['part_category_id'] = $row['part_category_id'];
				$filter_data_array['categories'][$i]['part_category'] = $row['part_category'];
				$i++;
			};

			$i = 0;
			while ($row = $availability_data_array->fetch()) {
				$filter_data_array['availability'][$i]['availability_id'] = $row['availability_id'];
				$filter_data_array['availability'][$i]['availability'] = $row['availability'];
				$i++;
			};

			$i = 0;
			while ($row = $condition_data_array->fetch()) {
				$filter_data_array['conditions'][$i]['part_condition_id'] = $row['part_condition_id'];
				$filter_data_array['conditions'][$i]['part_condition'] = $row['part_condition'];
				$i++;
			};

			if(is_array($filter_data_array)) {
				return $filter_data_array;
			} else return false;

		}

		public static function get_models ($value) {

			$pdo = Db::get_connection();

			$model_data_array = $pdo->query("
				SELECT model_id, model
				FROM models
				INNER JOIN con_brands_models ON models.model_id = con_brands_models.brands_models_model_id
				WHERE con_brands_models.brands_models_brand_id = '$value'
				");

			$i = 0;
			while ($row = $model_data_array->fetch()) {
				$models[$i]['model_id'] = $row['model_id'];
				$models[$i]['model'] = $row['model'];
				$i++;
			};

			if(isset($models) && is_array($models)) {
				return $models;
			} else return false;

		}

		public static function get_catalog_item($id) {

			$pdo = Db::get_connection();

			$catalog_item_pdo = $pdo->query("
				SELECT part_id, part_title, part_number, part_cost, part_discription, availability.availability, parts_conditions.part_condition, images.image, brands.brand, models.model, parts_categories.part_category
				FROM parts
				INNER JOIN parts_conditions On parts.part_condition_id = parts_conditions.part_condition_id
				INNER JOIN availability ON parts.part_availability_id = availability.availability_id
				INNER JOIN con_parts_images ON parts.part_id = con_parts_images.parts_images_part_id
				INNER JOIN parts_categories ON parts.part_category_id = parts_categories.part_category_id
				INNER JOIN images ON con_parts_images.parts_images_image_id = images.image_id
				INNER JOIN con_parts_brands ON parts.part_id = con_parts_brands.parts_brands_part_id
				INNER JOIN brands ON con_parts_brands.parts_brands_brand_id = brands.brand_id
				INNER JOIN con_parts_models ON parts.part_id = con_parts_models.parts_models_part_id
				INNER JOIN models ON con_parts_models.parts_models_model_id = models.model_id
				WHERE parts.part_id = '$id'
			");

			$row_count = $catalog_item_pdo->rowCount();

			$i = 0;
			while ($row = $catalog_item_pdo->fetch()) {
				$catalog_array['part_id'] = $row['part_id'];
				$catalog_array['title'] = $row['part_title'];
				$catalog_array['part_number'] = $row['part_number'];
				$catalog_array['cost'] = $row['part_cost'];
				$catalog_array['discription'] = $row['part_discription'];
				$catalog_array['category'] = $row['part_category'];
				$catalog_array['availability'] = $row['availability'];
				$catalog_array['condition'] = $row['part_condition'];
				$i++; 
			}

			$brand_item_pdo = $pdo->query("SELECT brands.brand
				FROM con_parts_brands
				INNER JOIN brands ON con_parts_brands.parts_brands_brand_id = brands.brand_id
				WHERE parts_brands_part_id = '$id'
			");

			$i = 0;
			while ($row = $brand_item_pdo->fetch()) {
				$catalog_array['brands'][$i] = $row['brand'];
				$i++; 
			}


			$model_item_pdo = $pdo->query("SELECT models.model
				FROM con_parts_models
				INNER JOIN models ON con_parts_models.parts_models_model_id = models.model_id
				WHERE parts_models_part_id = '$id'
			");

			$i = 0;
			while ($row = $model_item_pdo->fetch()) {
				$catalog_array['models'][$i] = $row['model'];
				$i++; 
			}

			$image_item_pdo = $pdo->query("SELECT images.image
				FROM con_parts_images
				INNER JOIN images ON con_parts_images.parts_images_image_id = images.image_id
				WHERE parts_images_part_id = '$id'
			");

			$i = 0;
			while ($row = $image_item_pdo->fetch()) {
				$catalog_array['images'][$i] = $row['image'];
				$i++; 
			}	

			if(is_array($catalog_array)) {
				return $catalog_array;
			} else return false;

		}

	}