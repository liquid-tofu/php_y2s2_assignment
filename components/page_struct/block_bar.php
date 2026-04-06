<section id="search-type">
  <label for="<?= $block_name ?>"><?= ucwords(strtolower($block_name)) ?></label>
  <div class="div-btn">
    <select name="<?= $block_name ?>" id="<?= $block_name ?>">
      <option value="none"    <?= ($search_block == 'none')    ? 'selected' : '' ?>>None</option>
      <?php
      $col_block = [];
      $list = $conn->query("SELECT DISTINCT $block_name FROM $tbl_name");
      if($list){
        $col_block = $list->fetch_all(MYSQLI_ASSOC);
      }

      foreach($col_block as $cell){
        $value = $cell[$block_name];
        $selected = (strcasecmp($search_block, $value) == 0) ? "selected" : "";
        echo '<option value="' . $value . '" ' . $selected . '>' . ucwords(strtolower($value)) . '</option>';
      }
      ?>
    </select>
  </div>
</section>
