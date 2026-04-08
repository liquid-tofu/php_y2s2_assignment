<section id="search-type">
  <label for="block"><?= ucfirst(strtolower($origin_src['block']['namis'] ?? '')); ?></label>
  <div class="div-btn">
    <select name="block" id="block">
      <option value="none" <?= ($search_block == 'none') ? 'selected' : '' ?>>None</option>
      <?php
      $columns = [];
      $list = $conn->query("
        SELECT DISTINCT {$origin_src['block']['column']} 
        FROM {$origin_src['block']['table']};
      ");
      if($list){
        $columns = $list->fetch_all(MYSQLI_ASSOC);
      }

      foreach($columns as $cell){
        $value = $cell[$origin_src['block']['column']];
        $selected = (strcasecmp($search_block, $value) == 0) ? "selected" : "";
        echo '<option value="' . $value . '" ' . $selected . '>' . ucwords(strtolower($value)) . '</option>';
      }
      ?>
    </select>
  </div>
</section>
