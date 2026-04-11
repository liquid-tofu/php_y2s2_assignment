<?php
$return_url = isset($_POST['return_url']) ? (string)$_POST['return_url'] : '';
$page = isset($_POST['page']) ? (string)$_POST['page'] : '';
$route_source = $return_url !== '' ? $return_url : $page;
$route_path = parse_url($route_source, PHP_URL_PATH) ?: '';
$route_dir = trim(str_replace('\\', '/', dirname($route_path)), '/');
$config_file = __DIR__ . '/../../' . ($route_dir !== '' ? $route_dir . '/' : '') . 'config.php';

if (!is_file($config_file)) {
  die('Config file not found for this page.');
}
require($config_file);
require('../header.php');
require('../sidebar.php');
require('../page_logic/func_compat.php');

// func for validate role
function require_role($page, ...$roles) {
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
  }
  if (!in_array(strtolower($_SESSION['role']), array_map('strtolower', $roles))) {
    $_SESSION['message'] = 'You do not have permission to do this.';
    header("Location: $page");
    exit;
  }
}

// security shtt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['message'] = 'Invalid request.';
  header('Location: index.php');
  exit;
}
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
  $_SESSION['message'] = 'Security validation failed.';
  header('Location: dashboard.php');
  exit;
}
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  $_SESSION['message'] = 'Invalid security token.';
  header('Location: dashboard.php');
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : '/index.php';
$page = isset($_POST['page']) ? $_POST['page'] : '/index.php';

// when click update button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $set_parts = [];
  $params = [];
  $types = "";

  foreach ($origin['columns'] as $key => $values) {
    $col = $values[1];
    $type = $values[3] ?? 'text';
    $editable = $values[4] ?? true;
    if (!$editable) continue;
    if (str_contains($col, "id") || str_contains($col, "created")) continue;

    $post_col = str_replace('.', '_', $col);
    if (!isset($_POST[$post_col])) {
      continue;
    }

    $posted_value = $_POST[$post_col];
    $db_col = $col;
    if (str_contains($db_col, '.')) {
      [$tbl_alias, $real_col] = explode('.', $db_col, 2);
      if ($tbl_alias === 'm') {
        $set_parts[] = "`$real_col` = ?";
        $params[] = $posted_value;
        $types .= ($type === 'number') ? "i" : "s";
        continue;
      }

      if (!empty($origin['joins'])) {
        foreach ($origin['joins'] as $j) {
          if (($j[1] ?? '') !== $tbl_alias) continue;
          $left = $j[2] ?? '';
          $right = $j[3] ?? '';
          $main_fk = null;
          $join_pk = null;
          if (str_starts_with($left, 'm.') && str_starts_with($right, $tbl_alias . '.')) {
            $main_fk = substr($left, 2);
            $join_pk = substr($right, strlen($tbl_alias) + 1);
          } elseif (str_starts_with($right, 'm.') && str_starts_with($left, $tbl_alias . '.')) {
            $main_fk = substr($right, 2);
            $join_pk = substr($left, strlen($tbl_alias) + 1);
          }
          if (!$main_fk || !$join_pk) continue;

          $ref_sql = "SELECT {$join_pk} AS id FROM {$j[0]} WHERE {$real_col} = ? LIMIT 1";
          $ref_stmt = $conn->prepare($ref_sql);
          if (!$ref_stmt) continue;
          $ref_stmt->bind_param("s", $posted_value);
          $ref_stmt->execute();
          $ref_result = $ref_stmt->get_result();
          $ref_row = $ref_result ? $ref_result->fetch_assoc() : null;
          if ($ref_row && isset($ref_row['id'])) {
            $set_parts[] = "`$main_fk` = ?";
            $params[] = (int)$ref_row['id'];
            $types .= "i";
          }
          break;
        }

        if (empty($set_parts)) {
          foreach ($origin['joins'] as $j) {
            if (($j[1] ?? '') !== $tbl_alias) continue;
            $target_table = $j[0] ?? '';
            $left = $j[2] ?? '';
            $right = $j[3] ?? '';
            $parent_alias = null;
            $parent_fk = null;
            $target_pk = null;

            if (str_starts_with($left, $tbl_alias . '.') && str_contains($right, '.')) {
              $parent_alias = explode('.', $right, 2)[0];
              $target_pk = explode('.', $left, 2)[1];
              $parent_fk = explode('.', $right, 2)[1];
            } elseif (str_starts_with($right, $tbl_alias . '.') && str_contains($left, '.')) {
              $parent_alias = explode('.', $left, 2)[0];
              $target_pk = explode('.', $right, 2)[1];
              $parent_fk = explode('.', $left, 2)[1];
            }

            if (!$parent_alias || $parent_alias === 'm') continue;

            foreach ($origin['joins'] as $k) {
              if (($k[1] ?? '') !== $parent_alias) continue;
              $left2 = $k[2] ?? '';
              $right2 = $k[3] ?? '';
              $main_fk_2 = null;
              $parent_pk = null;

              if (str_starts_with($left2, 'm.') && str_starts_with($right2, $parent_alias . '.')) {
                $main_fk_2 = explode('.', $left2, 2)[1];
                $parent_pk = explode('.', $right2, 2)[1];
              } elseif (str_starts_with($right2, 'm.') && str_starts_with($left2, $parent_alias . '.')) {
                $main_fk_2 = explode('.', $right2, 2)[1];
                $parent_pk = explode('.', $left2, 2)[1];
              }

              if (!$main_fk_2 || !$parent_pk) continue;

              $parent_table = $k[0] ?? '';
              if (!$parent_table || !$target_table || !$parent_fk || !$target_pk) continue;

              $ref_sql = "SELECT p.{$parent_pk} AS id
                          FROM {$parent_table} p
                          JOIN {$target_table} t ON p.{$parent_fk} = t.{$target_pk}
                          WHERE t.{$real_col} = ?
                          LIMIT 1";
              $ref_stmt = $conn->prepare($ref_sql);
              if (!$ref_stmt) continue;
              $ref_stmt->bind_param("s", $posted_value);
              $ref_stmt->execute();
              $ref_result = $ref_stmt->get_result();
              $ref_row = $ref_result ? $ref_result->fetch_assoc() : null;
              if ($ref_row && isset($ref_row['id'])) {
                $set_parts[] = "`$main_fk_2` = ?";
                $params[] = (int)$ref_row['id'];
                $types .= "i";
              }
              break 2;
            }
          }
        }
      }
    }
  }

  if (empty($set_parts)) {
    $_SESSION['message'] = 'No valid fields to update.';
    header("Location: $return_url");
    exit;
  }

  $params[] = $id;
  $types .= "i";

  $sql = "UPDATE {$origin['table']} SET " . implode(", ", $set_parts) . " WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  
  if ($stmt->execute()) {
    $_SESSION['message'] = 'Record updated successfully!';
    header("Location: $return_url");
    exit;
  }
  $err = method_exists($stmt, 'getErrorMessage') ? $stmt->getErrorMessage() : 'Update failed.';
  $_SESSION['message'] = $err ?: 'Update failed.';
  header("Location: $return_url");
  exit;
}

require_role($page, 'admin', 'manager');

$row = display($conn, [], [], '', $origin, 'single', $id);
if (!$row) {
  $_SESSION['message'] = 'Record not found.';
  header("Location: $page");
  exit;
}
?>

<div class="main">
  <div class="topbar">
    <h3>Stock Management System</h3>
    <div class="user">
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
    </div>
  </div>

  <div class="content">
    <div id="content-container">
      <h3><?= ucwords(strtolower($origin['page'] . " management")) ?></h3>
      <hr>
      <?php
      // notification
      if (isset($_SESSION['message'])) {
        echo '<div class="popup-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
      }
      ?>

      <form method="POST" class="edit-form" autocomplete="off">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="csrf_token" value="<?= $_POST['csrf_token'] ?>">
        <input type="hidden" name="return_url" value="<?= $_POST['return_url'] ?>">
        <input type="hidden" name="update" value="1">

        <?php foreach ($origin['columns'] as $key => $values): 
          $col = $values[1];
          $type = $values[3] ?? 'text';
          $editable = $values[4] ?? true;
          $post_col = str_replace('.', '_', $col);
          if (!$editable) continue;
          if (str_contains($col, "id") || str_contains($col, "created")) continue;
        ?>
          <section>
            <label><?= $key ?></label>
            <?php if ($type === "text"): ?>
              <input type="text" name="<?= $post_col ?>" value="<?= htmlspecialchars($row[$col]) ?>">

            <?php elseif ($type === "number"): ?>
              <input type="number" name="<?= $post_col ?>" value="<?= $row[$col] ?>" min="0" max="999">

            <?php elseif ($type === "datetime-local"): ?>
              <input type="datetime-local" name="<?= $post_col ?>" value="<?= date('Y-m-d\TH:i', strtotime($row[$col])) ?>">

            <?php else: ?>
              <select name="<?= $post_col ?>">
                <?php
                $opt_table = null;
                $opt_column = null;
                if (str_contains($col, '.')) {
                  [$opt_alias, $opt_field] = explode('.', $col, 2);
                  if ($opt_alias === 'm') {
                    $opt_table = $origin['table'];
                    $opt_column = $opt_field;
                  } elseif (!empty($origin['joins'])) {
                    foreach ($origin['joins'] as $j) {
                      if (($j[1] ?? '') === $opt_alias) {
                        $opt_table = $j[0];
                        $opt_column = $opt_field;
                        break;
                      }
                    }
                  }
                }
                if (!$opt_table || !$opt_column) {
                  $opt_table = $origin['block']['table'] ?? null;
                  $opt_column = $origin['block']['column'] ?? null;
                }
                $options = [];
                if ($opt_table && $opt_column) {
                  $list = $conn->query("SELECT DISTINCT {$opt_column} AS name FROM {$opt_table}");
                  if ($list) {
                    $options = $list->fetch_all(MYSQLI_ASSOC);
                  }
                }
                foreach ($options as $block):
                  $selected = ($row[$col] == $block['name']) ? 'selected' : '';
                ?>
                  <option value="<?= htmlspecialchars($block['name']) ?>" <?= $selected ?>><?= ucwords(strtolower($block['name'])) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </section>
        <?php endforeach; ?>

        <section>
          <a href="<?= $_POST['return_url'] ?>" id="cancel-btn">Cancel</a>
          <button type="submit" id="update-btn">Update</button>
        </section>
      </form>
    </div>
  </div>
</div>

<?php require('../footer.php'); ?>
