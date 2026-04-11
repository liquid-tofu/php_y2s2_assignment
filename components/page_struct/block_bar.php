<section id="search-type">
  <label for="block"><?= ucfirst(strtolower($origin['block']['namis'] ?? '')); ?></label>
  <div class="div-btn">
    <select name="block" id="block">
      <option value="none" <?= ($search_block == 'none') ? 'selected' : '' ?>>None</option>
      <?php
      // select existed group
      $columns = [];
      $list = $conn->query("
        SELECT DISTINCT {$origin['block']['column']} 
        FROM {$origin['block']['table']};
      ");
      if ($list) {
        $rows = $list->fetch_all(MYSQLI_ASSOC);
        // extract as normal list
        $values = array_column($rows, $origin['block']['column']);
      }

      foreach ($values as $value) {
        $selected = (strcasecmp($search_block, $value) == 0) ? "selected" : "";
        echo '<option value="' . $value . '" ' . $selected . '>' . ucwords(strtolower($value)) . '</option>';
      }
      ?>
    </select>
  </div>
</section>
