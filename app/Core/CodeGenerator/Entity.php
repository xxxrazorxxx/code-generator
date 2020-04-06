<?php


namespace App\Core\CodeGenerator;

use Illuminate\Http\File;
use SimpleXMLElement;
use function GuzzleHttp\Psr7\str;

class Entity
{
    /** @var string $class_name */
    private $class_name;

    /** @var string $namespace */
    private $namespace;

    /** @var string $table */
    private $table;

    /** @var string $path */
    private $path;

    /** @var array $primary_keys */
    private $primary_keys = [];

    /** @var SimpleXMLElement $entity */
    private $entity;

    /** @var array $hasOneRelations */
    private $relations = [];

    /** @var array $usesClasses */
    private $usesClasses = [];

    /** @var array $fillableAttributes */
    private $fillableAttributes = [];

    /** @var array $castsAttributes */
    private $castsAttributes = [];

    function __construct(SimpleXMLElement $entity)
    {
        $this->entity = $entity;

        $attributes = $entity->attributes();

        $this->table = (string) $attributes['table'];

        $this->parseClassNameAndNameSpace((string) $attributes['name']);
        $this->parseNodes();
    }

    function createModel()
    {
        $stub = $this->getTemplate('model');
        $stub = $this->replaceNamespace($stub);
        $stub = $this->replaceClassName($stub, ['Model']);
        $stub = $this->replaceTableName($stub);
        $stub = $this->replacePrimaryKeys($stub);
        $stub = $this->replaceRelations($stub);
        $stub = $this->replaceUses($stub);
        $stub = $this->replaceFillableAttributes($stub);
        $stub = $this->replaceCastsAttributes($stub);
        $this->createFile($stub);
        //print_r($stub);
    }

    private function parseNodes()
    {
        // Primary keys
        if (isset($this->entity->id)) {
            $primary_keys = $this->entity->id->children();

            foreach ($primary_keys as $key => $property) {
                $this->primary_keys[] = $this->getArrayFromXml($property);;
            }
        }

        //Relations HasOne
        foreach ($this->entity->hasOne as $hasOneRelation) {
            $relation = $this->getArrayFromXml($hasOneRelation);
            $relation['type'] = 'hasOne';
            $this->relations[] = $relation;
        }

        //Relations hasMany
        foreach ($this->entity->hasMany as $hasManyRelation) {
            $relation = $this->getArrayFromXml($hasManyRelation);
            $relation['type'] = 'hasMany';
            $this->relations[] = $relation;
        }

        //Relations belongsTo
        foreach ($this->entity->belongsTo as $belongsToRelation) {
            $relation = $this->getArrayFromXml($belongsToRelation);
            $relation['type'] = 'belongsTo';
            $this->relations[] = $relation;
        }

        //Relations belongsToMany
        foreach ($this->entity->belongsToMany as $belongsToManyRelation) {
            $relation = $this->getArrayFromXml($belongsToManyRelation);
            $relation['type'] = 'belongsToMany';
            $this->relations[] = $relation;
        }

        //Relations morphOne
        foreach ($this->entity->morphOne as $morphOneRelation) {
            $relation = $this->getArrayFromXml($morphOneRelation);
            $relation['type'] = 'morphOne';
            $this->relations[] = $relation;
        }

        //Relations morphMany
        foreach ($this->entity->morphMany as $morphManyRelation) {
            $relation = $this->getArrayFromXml($morphManyRelation);
            $relation['type'] = 'morphMany';
            $this->relations[] = $relation;
        }

        //Relations morphToMany
        foreach ($this->entity->morphToMany as $morphToManyRelation) {
            $relation = $this->getArrayFromXml($morphToManyRelation);
            $relation['type'] = 'morphToMany';
            $this->relations[] = $relation;
        }
    }

    /**
     * Generates array from the SimpleXMLElement
     *
     * @param SimpleXMLElement $XMLElement
     *
     * @return array
     */
    private function getArrayFromXml(SimpleXMLElement $XMLElement)
    {
        $attributes = $XMLElement->attributes();
        $array = [];
        foreach ($attributes as $attr_key => $value) {
            $array[(string) $attr_key] = (string) $value;
        }

        return $array;
    }

    /**
     * Creates php file of the Model
     *
     * @param string $content
     *
     * @return void
     */
    private function createFile($content)
    {
        $dir_list = explode('/', $this->path);
        $path = 'app/';
        foreach ($dir_list as $directory) {
            $path .= $directory . '/';
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

        $file = __DIR__ . '/../..' . $this->path . '/' . $this->class_name . '.php';
        if (!file_exists($file)) {
            file_put_contents($file, $content);
        } else {
            //TODO: Error Model already exists
        }
    }

    /**
     * Replaces [% namespace %] with the namespace of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceNamespace($template)
    {
        return str_replace('[% namespace %]', $this->namespace, $template);
    }

    /**
     * Replaces [% model_name_class %] with the class name of the Entity
     *  and [% model_extends %] with extended classes
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceClassName($template, array $extends)
    {
        $template = str_replace('[% model_name_class %]', $this->class_name, $template);
        $extends_str = implode(', ', $extends);
        return str_replace('[% model_extends %]', 'extends ' . $extends_str, $template);
    }

    /**
     * Replaces [% table %] with the table name of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceTableName($template)
    {
        return str_replace('[% table %]', $this->table, $template);
    }

    /**
     * Replaces [% primary_key %] with the primary_keys of the Entity
     *
     * P.S. Исходя из описания XML схемы я понял, что primary_key может быть несколько,
     *              поэтому пока сделал шаблон под массив.
     *
     * @param string $template
     *
     * @return string
     */
    private function replacePrimaryKeys($template)
    {
        $primary_key_names = [];
        $primary_key_template = $this->getTemplate('model-primary-key');

        foreach ($this->primary_keys as $primary_key) {
            $primary_key_names[] = sprintf('\'%s\'', $primary_key['name']);
        }

        $primary_key_template = str_replace(
            '[% primary_key %]',
            implode(', ', $primary_key_names),
            $primary_key_template
        );

        return str_replace('[% primary_key %]', $primary_key_template, $template);
    }

    /**
     * Replaces [% relationships %] with the relations of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceRelations($template)
    {
        $relation_string = '';

        foreach ($this->relations as $relation) {
            $class = 'App' . $relation['targetEntity'];
            $this->usesClasses[] = $class;

            $relation_params = [];
            $relation_params[] = sprintf('\'%s\'', $class);

            if (isset($relation['foreignKey'])) {
                $relation_params[] = sprintf('\'%s\'', $relation['foreignKey']);
            }

            if (isset($relation['key'])) {
                $relation_params[] = sprintf('\'%s\'', $relation['key']);
            }

            $relation_template = $this->getTemplate('model-relations');
            $relation_template = str_replace('[% relation_name %]', $relation['property'], $relation_template);
            $relation_template = str_replace('[% relation_type %]', $relation['type'], $relation_template);
            $relation_template = str_replace(
                '[% relation_params %]',
                implode(', ', $relation_params),
                $relation_template
            );

            $relation_string .= $relation_template . PHP_EOL;
        }

        return str_replace('[% relationships %]', $relation_string, $template);
    }

    /**
     * Replaces [% use_command_placeholder %] with the uses classes of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceUses($template)
    {

        return $template;
    }

    /**
     * Replaces [% fillable %] with the fillable attributes of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceFillableAttributes($template)
    {
        $attributes = [];
        foreach ($this->fillableAttributes as $attribute) {
            $attributes[] = sprintf('\'%s\'', $attribute);
        }

        return str_replace('[% fillable %]', implode(', ', $attributes), $template);
    }

    /**
     * Replaces [% casts %] with the casts attributes of the Entity
     *
     * @param string $template
     *
     * @return string
     */
    private function replaceCastsAttributes($template)
    {
        $attributes = [];
        foreach ($this->castsAttributes as $key => $value) {
            $attributes[] = sprintf('\'%s\' => \'%s\'', $key, $value);
        }

        return str_replace('[% casts %]', implode(', ', $attributes), $template);
    }

    /**
     * Returns template file by te type
     *
     * @param string $type
     *
     * @return string
     */
    private function getTemplate($type)
    {
        $template = '';
        $path = __DIR__ . '/../../../resources/code_templates/' . $type . '.stub';
        if (file_exists($path)) {
            $template = file_get_contents($path);
        }
        return $template;
    }

    /**
     * Parses class name and namespace from the xml
     *
     * @param string $model_name
     *
     * return void
     */
    private function parseClassNameAndNameSpace($model_name)
    {
        $dirs = explode('\\', $model_name);
        $this->class_name = array_pop($dirs);
        $this->path = implode('/', $dirs);
        $dirs[0] = 'App';
        $this->namespace = implode('\\', $dirs);
    }
}
