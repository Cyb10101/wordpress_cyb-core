<?php
namespace App\Utility;

class WpFormUtility extends Singleton {
    public function renderCheckbox(string $task, string $name, bool $isChecked = false, array $arguments = []) {
        $arguments = wp_parse_args($arguments, [
            'required' => false,
            'label' => 'Checkbox',
            'help' => '',
        ]);

        $required = $arguments['required'] ? ' required' : '';
        $checked = $isChecked ? ' checked' : '';
        ?><div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="<?php echo $name; ?>" id="<?php echo $task . '_' . $name; ?>"<?php echo $checked . $required; ?>>
            <label class="form-check-label" for="<?php echo $task . '_' . $name; ?>">
                <?php echo $arguments['label']; ?>
            </label>
            <br>
            <?php if (!empty($arguments['help'])) { ?>
                <small id="<?php echo $task . '_' . $name; ?>-help" class="form-text text-muted">
                    <?php echo $arguments['help']; ?>
                </small>
            <?php } ?>
        </div><?php
    }

    public function renderTextBox(string $task, string $name, string $value, array $arguments = []) {
        $arguments = wp_parse_args($arguments, [
            'required' => false,
            'label' => 'Checkbox',
            'help' => '',
            'rows' => '5',
            'placeholder' => '',
            'pattern' => '',
        ]);

        $required = $arguments['required'] ? ' required' : '';
        $placeholder = !empty($arguments['placeholder']) ? ' placeholder="' . $arguments['placeholder'] . '"' : '';
        $pattern = !empty($arguments['pattern']) ? ' pattern="' . $arguments['pattern'] . '"' : '';
        ?><div class="form-check mb-2">
            <label for="<?php echo $task . '_' . $name; ?>">
                <?php echo $arguments['label']; ?>
            </label>
            <input type="text" class="form-control" name="<?php echo $name; ?>" id="<?php echo $task . '_' . $name; ?>" value="<?php echo $value; ?>" aria-describedby="<?php echo $task . '_' . $name; ?>-help"<?php echo $placeholder . $pattern . $required; ?>>
            <?php if (!empty($arguments['help'])) { ?>
                <small id="<?php echo $task . '_' . $name; ?>-help" class="form-text text-muted">
                    <?php echo $arguments['help']; ?>
                </small>
            <?php } ?>
        </div><?php
    }

    public function renderTextArea(string $task, string $name, string $value, array $arguments = []) {
        $arguments = wp_parse_args($arguments, [
            'required' => false,
            'label' => 'Checkbox',
            'help' => '',
            'rows' => '5',
        ]);

        $required = $arguments['required'] ? ' required' : '';
        ?><div class="form-check mb-2">
            <label for="<?php echo $task . '_' . $name; ?>">
                <?php echo $arguments['label']; ?>
            </label>
            <textarea class="form-control" name="<?php echo $name; ?>" id="<?php echo $task . '_' . $name; ?>" rows="<?php echo $arguments['rows']; ?>"<?php echo $required; ?>><?php echo $value; ?></textarea>
            <?php if (!empty($arguments['help'])) { ?>
                <small id="<?php echo $task . '_' . $name; ?>-help" class="form-text text-muted">
                    <?php echo $arguments['help']; ?>
                </small>
            <?php } ?>
        </div><?php
    }

    public function renderButtonSubmit(string $task, array $arguments = []) {
        $arguments = wp_parse_args($arguments, [
            'label' => 'Save changes',
            'help' => '',
        ]);

        ?><div class="form-group">
            <button type="submit" class="btn btn-primary btn-spinner">
                <?php echo _e($arguments['label'], 'cyb-core');?>
                <div class="spinner-border" role="status"></div>
            </button>
            <br>
            <?php if (!empty($arguments['help'])) { ?>
                <small id="<?php echo $task . '_submit'; ?>-help" class="form-text text-muted">
                    <?php echo $arguments['help']; ?>
                </small>
            <?php } ?>
        </div><?php
    }
}
