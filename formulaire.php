<?php
require_once 'class_formgenerator.php';
session_start();
//session_destroy();
if (isset($_POST['reset'])) {
    unset($_SESSION['fields']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$form = new FormGenerator($_SERVER['PHP_SELF'], "POST");
if (isset($_SESSION['fields'])) {
    foreach ($_SESSION['fields'] as $field) {
        $form->addField($field['name'], $field['type'], $field['label'], $field['attributes']);
    }
}
if (isset($_POST['ajouter'])) {
    $fieldType = $_POST['fieldType'];
    $fieldName = $_POST['fieldName'];
    $fieldLabel = $_POST['fieldLabel'];
    $fieldRequired = $_POST['fieldRequired'] === 'true';
    $fieldClass = $_POST['fieldClass'];
    $fieldId = $_POST['fieldId'];
    $fieldAttributes = [
        'required' => $fieldRequired ? 'required' : '',
        'class' => $fieldClass,
        'id' => $fieldId
    ];
    if ($fieldType === 'select') {
        $fieldAttributes['options'] = [
            ['value' => 'option1', 'label' => 'Option 1'],
            ['value' => 'option2', 'label' => 'Option 2']
        ];
    }
    $form->addField($fieldName, $fieldType, $fieldLabel, $fieldAttributes);
    $_SESSION['fields'][] = [
        'name' => $fieldName,
        'type' => $fieldType,
        'label' => $fieldLabel,
        'attributes' => $fieldAttributes
    ];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['ajouter']) && !isset($_POST['reset'])) {
    if ($form->handleSubmission()) {
        echo "<p style='color: green;'>Le formulaire a été soumis avec succès !</p>";
        unset($_SESSION['fields']);
    } else {
        echo '<p style="color: red;">Veuillez corriger les erreurs ci-dessous.</p>';
    }
}
?>
    <form action="" method="post">
        <label for="fieldType">Type de champ :</label>
        <select name="fieldType" id="fieldType">
            <option value="text">Text</option>
            <option value="email">Email</option>
            <option value="textarea">Textarea</option>
            <option value="select">Select</option>
            <option value="file">File</option>
        </select>
        <label for="fieldName">Nom du champ :</label>
        <input type="text" name="fieldName" id="fieldName" required>
        <label for="fieldLabel">Label du champ :</label>
        <input type="text" name="fieldLabel" id="fieldLabel" required>
        <label for="fieldRequired">Requis :</label>
        <select name="fieldRequired" id="fieldRequired">
            <option value="true">Oui</option>
            <option value="false">Non</option>
        </select>
        <label for="fieldClass">Classe CSS :</label>
        <input type="text" name="fieldClass" id="fieldClass">
        <label for="fieldId">ID :</label>
        <input type="text" name="fieldId" id="fieldId">
        <button type="submit" name="ajouter">Ajouter le champ</button>
    </form>
<?php
if (!empty($_SESSION['fields'])) {
    $form->render();
    echo '<form action="" method="post">';
    echo '<button type="submit" name="reset">Supprimer le formulaire</button>';
    echo '</form>';
}
?>