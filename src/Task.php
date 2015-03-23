<?php
  class Task {
    private $description;
    private $due_date;
    private $id;

    function __construct($description, $id = null, $due_date = '1900/01/01')   {
      $this->description = $description;
      $this->due_date = $due_date;
      $this->id = $id;
    }

    // getters
    function getDescription()  {
      return $this->description;
    }

    function getId() {
      return $this->id;
    }

    function getDueDate() {
      return $this->due_date;
    }

    // setters
    function setDescription($new_desc)  {
      $this->description = (string) $new_desc;
    }

    function setId($id) {
      $this->id = (int) $id;
    }

    function setDueDate($due_date) {
      $this->due_date = $due_date;
    }

    // DB


    /* 1. Run query method on superglobal variable DB (to_do database);
          insert description and category id from this object into the
          database and gets the id value from that row (which has been
          generated by the database); stores PDO db object of the newly-created
          row in a variable called statement.
       2. Run fetch method on statement object which returns associative array
          containing the values from that row assigned to keys which correspond
          to columns in db.
       3. Set id variable of THIS object to value of id generated by the DB.
    */
    function save() {
      $statement = $GLOBALS['DB']->query("INSERT INTO tasks (description, due_date) VALUES ('{$this->getDescription()}', '{$this->getDueDate()}') RETURNING id;");
      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $this->setId($result['id']);
    }

    function addCategory($category) {
      $GLOBALS['DB']->exec("INSERT INTO categories_tasks (category_id, task_id) VALUES ({$category->getId()}, {$this->getId()});");
    }

    function getCategories() {
      $query = $GLOBALS['DB']->query("SELECT category_id FROM categories_tasks WHERE task_id = {$this->getId()};");
      $category_ids = $query->fetchAll(PDO::FETCH_ASSOC);

      $categories = [];
      foreach ($category_ids as $id) {
        $category_id = $id['category_id'];
        $result = $GLOBALS['DB']->query("SELECT * FROM categories WHERE id = {$category_id};");
        $returned_category = $result->fetchAll(PDO::FETCH_ASSOC);

        $name = $returned_category[0]['name'];
        $id = $returned_category[0]['id'];
        $new_category = new Category($name, $id);
        array_push($categories, $new_category);
      }
      return $categories;
    }

    /* 1. Create null variable found_task; will be returned by function.
       2. Get all existing Task objects; assign to new array called tasks.
       3. Compare task_id for each task object to passed argument search_id.
       4. If the two above values are equal, assign task to found_task.
       5. Return found_task.
    */
    static function find($search_id) {
      $found_task = null;
      $tasks = Task::getAll();
      foreach ($tasks as $task) {
        $task_id = $task->getId();
        if ($task_id == $search_id) {
          $found_task = $task;
        }
      }
      return $found_task;
    }

    /* 1. Get all rows from DB and assign to variable returned_tasks.
       2. Create empty array called tasks; will be returned by function.
       3. Get values from each column of returned_tasks; assign to temp
          variables.
       4. Instantiate new Task object passing temp variables from row.
       5. Push new Task to tasks array.
       6. Return tasks array.
    */
    static function getAll() {
      $returned_tasks = $GLOBALS['DB']->query("SELECT * FROM tasks;");
      $tasks = array();
      foreach($returned_tasks as $task) {
        $description = $task['description'];
        $id = $task['id'];
        $due_date = $task['due_date'];
        $due_date = str_replace("-", "/", $due_date);
        $new_task = new Task($description, $id, $due_date);
        array_push($tasks, $new_task);
      }
      return $tasks;
    }

    /* 1. Run exec method on DB (to_do) which runs the DELETE sql command with
          wildcard to delete all existing rows in DB.
    */
    static function deleteAll() {
      $GLOBALS['DB']->exec("DELETE FROM tasks *;");
    }
  }
?>
