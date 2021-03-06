<?php

/**
 * @file
 * Contains \Drupal\database_test\Controller\DatabaseTestController.
 */

namespace Drupal\database_test\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller routines for database_test routes.
 */
class DatabaseTestController {

  /**
   * Runs db_query_temporary() and outputs the table name and its number of rows.
   *
   * We need to test that the table created is temporary, so we run it here, in a
   * separate menu callback request; After this request is done, the temporary
   * table should automatically dropped.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function dbQueryTemporary() {
    $table_name = db_query_temporary('SELECT age FROM {test}', array());
    return new JsonResponse(array(
      'table_name' => $table_name,
      'row_count' => db_select($table_name)->countQuery()->execute()->fetchField(),
    ));
  }

  /**
   * Runs a pager query and returns the results.
   *
   * This function does care about the page GET parameter, as set by the
   * simpletest HTTP call.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function pagerQueryEven($limit) {
    $query = db_select('test', 't');
    $query
      ->fields('t', array('name'))
      ->orderBy('age');

    // This should result in 2 pages of results.
    $query = $query
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit($limit);

    $names = $query->execute()->fetchCol();

    return new JsonResponse(array(
      'names' => $names,
    ));
  }

  /**
   * Runs a pager query and returns the results.
   *
   * This function does care about the page GET parameter, as set by the
   * simpletest HTTP call.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function pagerQueryOdd($limit) {
    $query = db_select('test_task', 't');
    $query
      ->fields('t', array('task'))
      ->orderBy('pid');

    // This should result in 4 pages of results.
    $query = $query
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit($limit);

    $names = $query->execute()->fetchCol();

    return new JsonResponse(array(
      'names' => $names,
    ));
  }

  /**
   * Runs a tablesort query and returns the results.
   *
   * This function does care about the page GET parameter, as set by the
   * simpletest HTTP call.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function testTablesort() {
    $header = array(
      'tid' => array('data' => t('Task ID'), 'field' => 'tid', 'sort' => 'desc'),
      'pid' => array('data' => t('Person ID'), 'field' => 'pid'),
      'task' => array('data' => t('Task'), 'field' => 'task'),
      'priority' => array('data' => t('Priority'), 'field' => 'priority', ),
    );

    $query = db_select('test_task', 't');
    $query
      ->fields('t', array('tid', 'pid', 'task', 'priority'));

    $query = $query
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->orderByHeader($header);

    // We need all the results at once to check the sort.
    $tasks = $query->execute()->fetchAll();

    return new JsonResponse(array(
      'tasks' => $tasks,
    ));
  }

  /**
   * Runs a tablesort query with a second order_by after and returns the results.
   *
   * This function does care about the page GET parameter, as set by the
   * simpletest HTTP call.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function testTablesortFirst() {
    $header = array(
      'tid' => array('data' => t('Task ID'), 'field' => 'tid', 'sort' => 'desc'),
      'pid' => array('data' => t('Person ID'), 'field' => 'pid'),
      'task' => array('data' => t('Task'), 'field' => 'task'),
      'priority' => array('data' => t('Priority'), 'field' => 'priority', ),
    );

    $query = db_select('test_task', 't');
    $query
      ->fields('t', array('tid', 'pid', 'task', 'priority'));

    $query = $query
      ->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->orderByHeader($header)
      ->orderBy('priority');

    // We need all the results at once to check the sort.
    $tasks = $query->execute()->fetchAll();

    return new JsonResponse(array(
      'tasks' => $tasks,
    ));
  }
}
