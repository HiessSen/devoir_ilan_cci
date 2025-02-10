<?php
require_once 'interface_form.php';
class FormGenerator implements FormGeneratorInterface {
    private $action;
    private $method;
    private $fields = [];
    private $errors = [];
    public function __construct(string $action, string $method)
    {
        $this->action = $action;
        $this->method = $method;
    }
    public function addField(string $name, string $type, string $label, array $attributes = []): void
    {
        $this->fields[] = [
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'attributes' => $attributes
        ];
    }
    public function render(): void {
        echo '<form action="' . $this->action . '" method="' . $this->method . '">';
        foreach ($this->fields as $field) {
            echo '<label for="' . $field['name'] . '">' . $field['label'] . '</label>';
            if ($field['type'] === 'textarea') {
                echo '<textarea name="' . $field['name'] . '" id="' . $field['name'] . '"';
                $this->renderAttributes($field['attributes']);
                echo '></textarea><br>';
            } elseif ($field['type'] === 'select') {
                echo '<select name="' . $field['name'] . '" id="' . $field['name'] . '"';
                echo '<option value="option1">Option 1</option>';
                echo '<option value="option2">Option 2</option>';
                $this->renderAttributes($field['attributes'], ['options']);
                echo '>';
                foreach ($field['attributes']['options'] as $option) {
                    echo '<option value="' . htmlspecialchars($option) . '">' . $option . '</option>';
                }
                echo '</select><br>';
            } else {
                echo '<input type="' . $field['type'] . '" name="' . $field['name'] . '" id="' . $field['name'] . '"';
                $this->renderAttributes($field['attributes']);
                echo '><br>';
            }
            if (isset($this->errors[$field['name']])) {
                echo '<span style="color: red;"> ' . $this->errors[$field['name']] . '</span>';
            }
        }
        echo '<button type="submit">Submit</button>';
        echo '</form>';
    }
    private function renderAttributes(array $attributes, array $exclude = []): void
    {
        foreach ($attributes as $key => $value)
        {
            if (!in_array($key, $exclude))
            {
                if (is_array($value))
                {
                    $value = implode(' ', $value);
                }
                if ($value !== '')
                {
                    echo ' ' . $key . '="' . htmlspecialchars($value) . '"';
                }
            }
        }
    }
    public function handleSubmission(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === strtoupper($this->method))
        {
            foreach ($this->fields as $field)
            {
                $name = $field['name'];
                $label = $field['label'];
                $value = $_POST[$name] ?? '';
                $errorMessage = $field['attributes']['error_message'] ?? "$label est requis.";
                if (!empty($field['attributes']['required']) && empty($value))
                {
                    $this->errors[$name] = $errorMessage;
                }
                if ($field['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL))
                {
                    $this->errors[$name] = 'Le format de l\'email est invalide.';
                }
                if ($field['type'] === 'textarea' && strlen($value) < 10)
                {
                    $this->errors[$name] = 'Le message doit contenir au moins 10 caractères.';
                }
                if ($field['type'] === 'file')
                {
                    if (!isset($_FILES[$name])) {
                        $this->errors[$name] = $errorMessage;
                    }
                    else
                    {
                        $fileType = mime_content_type($_FILES[$name]['tmp_name']);
                        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                        if (!in_array($fileType, $allowedTypes)) {
                            $this->errors[$name] = 'Type de fichier non autorisé.';
                        }
                        if ($_FILES[$name]['size'] > 2 * 1024 * 1024) {
                            $this->errors[$name] = 'La taille du fichier ne doit pas dépasser 2 Mo.';
                        }
                    }
                }
            }
            return empty($this->errors);
        }
        return false;
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
}