<?php
/** Compare parsed code after resolving imports and normalizing formatter-only changes. */
require 'E:/.agents/tools/php-qa/vendor/autoload.php';

$root = dirname(__DIR__, 3);
$work = 'E:/tmp/wtcdek-style-package-20261015';
$parser = (new PhpParser\ParserFactory())->createForNewestSupportedVersion();

function normalized($value)
{
    if (is_array($value)) {
        $out = [];
        foreach ($value as $key => $item) {
            if ($item instanceof PhpParser\Node\Stmt\Use_ || $item instanceof PhpParser\Node\Stmt\GroupUse) {
                continue;
            }
            $out[] = normalized($item);
        }
        return $out;
    }
    if (!$value instanceof PhpParser\Node) {
        return $value;
    }
    if ($value instanceof PhpParser\Node\Expr\FuncCall && $value->name instanceof PhpParser\Node\Name) {
        $name = $value->name->toString();
        if (strpos($name, '\\') === false && function_exists($name)) {
            $value->name = new PhpParser\Node\Name\FullyQualified($name);
        }
    }
    if ($value instanceof PhpParser\Node\Scalar\String_) {
        return ['nodeType' => 'Scalar_String', 'value' => str_replace("\r\n", "\n", $value->value === 'UpdateWtCdekData' ? 'updateWtCdekData' : $value->value)];
    }
    if ($value instanceof PhpParser\Node\Stmt\ClassMethod && $value->isPrivate() && $value->name->toString() === 'UpdateWtCdekData') {
        $value->name = new PhpParser\Node\Identifier('updateWtCdekData');
    }
    if ($value instanceof PhpParser\Node\Stmt\InlineHTML) {
        return ['nodeType' => 'Stmt_InlineHTML', 'value' => preg_replace('/\s+/', ' ', $value->value)];
    }
    $out = ['nodeType' => $value->getType()];
    foreach ($value->getSubNodeNames() as $name) {
        $out[$name] = normalized($value->$name);
    }
    if ($value instanceof PhpParser\Node\Expr\BinaryOp\Concat && ($out['left']['nodeType'] ?? '') === 'Scalar_String' && ($out['right']['nodeType'] ?? '') === 'Scalar_String') {
        return ['nodeType' => 'Scalar_String', 'value' => $out['left']['value'] . $out['right']['value']];
    }
    return $out;
}

function parsed($parser, $file)
{
    $ast = $parser->parse(file_get_contents($file));
    $traverser = new PhpParser\NodeTraverser(new PhpParser\NodeVisitor\NameResolver());
    return normalized($traverser->traverse($ast));
}

$files = json_decode(file_get_contents(__DIR__ . '/source-before.json'), true);
$results = [];
foreach ($files as $name => $hash) {
    if (substr($name, -4) !== '.php') {
        continue;
    }
    $before = parsed($parser, $work . '/source-before/' . $name);
    $after = parsed($parser, $root . '/' . $name);
    $same = $before === $after;
    $results[] = ['file' => $name, 'same_normalized_ast' => $same];
    if (!$same) {
        $target = $work . '/ast/' . str_replace('/', '_', $name);
        if (!is_dir(dirname($target))) { mkdir(dirname($target), 0777, true); }
        file_put_contents($target . '.before.json', json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($target . '.after.json', json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
file_put_contents(__DIR__ . '/ast-comparison.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo json_encode(['files' => count($results), 'same' => count(array_filter($results, static fn($r) => $r['same_normalized_ast'])), 'different' => array_values(array_filter($results, static fn($r) => !$r['same_normalized_ast']))]);
