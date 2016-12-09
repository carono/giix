<?php
/**
 * This is the template for generating the model class of a specified table.
 * DO NOT EDIT THIS FILE! It may be regenerated with Gii.
 *
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name => label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name => relation declaration)
 */
$relationClasses = [];
$r = [];
foreach ($relations as $name => $relation){
    if (preg_match('/.*hasOne.*\\\(.*)::className\(\).*\[.*\s=>\s\'(.*)\'\]/',$relation[0],$m)) {
        $relationClasses[] = "'$m[2]'=>'{$generator->ns}" . '\\' . "{$relation[1]}'";
        $r[$name] = $m[1];
    }
    if (preg_match('/.*hasMany.*\\\(.*)::className\(\).*\[.*\s=>\s\'.*\'\]/',$relation[0],$m)) {
        $r[$name] = $m[1];
    }
}
$relationClasses = join(",\r",$relationClasses );
echo "<?php\n";

//print_r($relationClasses);
//exit;
?>

namespace <?= $generator->ns ?>\base;

use Yii;
<?php if (isset($translation)): ?>
use dosamigos\translateable\TranslateableBehavior;
<?php endif; ?>

/**
 * This is the base-model class for table "<?= $tableName ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property \<?=$ns?>\<?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 * @method static \<?= $generator->ns ."\\".$className ?> findOne($condition)
 * @method static \<?= $generator->ns ."\\".$className ?>[] findAll($condition)
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . "\n" ?>
{

<?php
    $traits = $generator->baseTraits;
    if ($traits) {
        echo "use {$traits};";
    }
?>
protected $_relationClasses = <?= $relationClasses ? '['. $relationClasses .']': '[]'?>;

<?php
if(!empty($enum)){
?>
    /**
    * ENUM field values
    */
<?php
    foreach($enum as $column_name => $column_data){
        foreach ($column_data['values'] as $enum_value){
            echo '    const ' . $enum_value['const_name'] . ' = \'' . $enum_value['value'] . '\';' . PHP_EOL;
        }
    }
?>
    var $enum_labels = false;
<?php
}
?>
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '<?= $tableName ?>';
    }
<?php if (isset($translation)): ?>
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'translatable' => [
                'class' => TranslateableBehavior::className(),
                // in case you renamed your relation, you can setup its name
                // 'relation' => 'translations',
<?php if ($generator->languageCodeColumn !== 'language'): ?>
                'languageField' => '<?= $generator->languageCodeColumn ?>',
<?php endif; ?>
                'translationAttributes' => [
                    <?= "'" . implode("',\n                    '", $translation['fields']) . "'\n" ?>
                ]
            ],
        ];
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . "\n        " ?>];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \<?=$generator->queryNs .'\\'. (isset($r[$name]) ? $r[$name] : '?')."Query\n" ?>
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
    public function getRelationClass($attribute)
    {
        return isset($this->_relationClasses[$attribute]) ? $this->_relationClasses[$attribute] : null;
    }
<?php if (isset($translation)): ?>
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        <?= $translation['code'] . "\n"?>
    }
<?php endif; ?>

<?php if ($queryClassName): ?>
    <?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
    ?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>

<?php
    foreach($enum as $column_name => $column_data){
?>

    /**
     * get column <?php echo $column_name?> enum value label
     * @param string $value
     * @return string
     */
    public static function <?php echo $column_data['func_get_label_name']?>($value){
        $labels = self::<?php echo $column_data['func_opts_name']?>();
        if(isset($labels[$value])){
            return $labels[$value];
        }
        return $value;
    }

    /**
     * column <?php echo $column_name?> ENUM value labels
     * @return array
     */
    public static function <?php echo $column_data['func_opts_name']?>()
    {
        return [
<?php
        foreach($column_data['values'] as $k => $value){
?>
            self::<?php echo $value['const_name'];?> => <?php echo $generator->generateString($value['label'])?>,
<?php
        }
?>
        ];
    }
<?php
    }


?>

}
