<?php
/**
 * Copyright 2013, Joost de Keijzer (http://dekeijzer.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2013, Joost de Keijzer (http://dekeijzer.org)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('SearchableBehavior', 'Search.Model/Behavior');

class I18nSearchableBehavior extends SearchableBehavior {
	public $i18nBindings = array();

/**
 * find on i18 content
 */
	public function findI18n(Model $Model, $type = 'first', $query = array()) {
		if ($Model->Behaviors->enabled('Translate') && isset($query['conditions'])) {
			$query = $this->_i18nQuery($Model, $query);
		}

		return $Model->find($type, $query);
	}

/**
 * Loop through query parameters and i18n them
 */
	protected function _i18nQuery(Model $Model, $query = array()) {
		if (!isset($this->i18nBindings[$Model->alias])) {
			$this->i18nBindings[$Model->name] = array();
			foreach ( $Model->Behaviors->Translate->settings[$Model->alias] as $k => $v ) {
				if ( is_numeric( $k ) ) {
					$this->i18nBindings[$Model->alias][$v] = "I18n__{$v}.content";
				} else {
					// with binding, record
					$this->i18nBindings[$Model->alias][$k] = "I18n__{$k}.content"; // bound translations are set later in de find process
				}
			}
		}

		$fields = array();
		$this->_queryWalk($Model, $query['conditions'], $fields, $this->i18nBindings[$Model->alias]);

		if (isset($query['fields'])) {
			foreach ($fields as $field) {
				if (!in_array($field, $query['fields'])) {
					$query['fields'][] = $field;
				}
			}
		}

		return $query;
	}

	protected function _queryWalk(Model $Model, &$conditions = array(), &$fields = array(), $i18nBindings) {
		foreach (array_keys($conditions) as $key) {
			$value = $conditions[$key];
			if (is_string($key)) {
				foreach (array_keys($i18nBindings) as $field) {
					if ("{$Model->alias}.$field" == substr($key, 0, strlen("{$Model->alias}.$field"))) {
						unset($conditions[$key]);
						$key = substr_replace($key, $i18nBindings[$field], 0, strlen("{$Model->alias}.$field"));
						$conditions[$key] = $value;
						$fields[] = $field;
					} else if ($field == substr($key, 0, strlen($field))) {
						unset($conditions[$key]);
						$key = substr_replace($key, $i18nBindings[$field], 0, strlen($field));
						$conditions[$key] = $value;
						$fields[] = $field;
					}
				}
			}

			if (is_array($value)) {
				$this->_queryWalk($Model, $conditions[$key], $fields, $i18nBindings);
			}
		}
	}
}
