<?php
/**
 * Created by PhpStorm.
 * Project: yii2_clear
 * User: lxShaDoWxl
 * Date: 10.06.16
 * Time: 14:38
 */
namespace shadow\sgii;

use PhpParser\PrettyPrinter;
use PhpParser\Node\Expr;
use PhpParser\Node;

class PrinterCode extends PrettyPrinter\Standard
{
//    protected $_level=0;
//    public function pExpr_Array(Expr\Array_ $node)
//    {
//        if(!$this->_level){
//            $this->_level=1;
//            $ret="array(\n\t" . $this->pImplode($node->items,",\n\t") . "\n)";
//            $this->_level=0;
//            return $ret;
//        }else
//            return parent::pExpr_Array($node);
//    }
    public function pExpr_Array(Expr\Array_ $node)
    {
        $syntax = $node->getAttribute('kind',
            $this->options['shortArraySyntax'] ? Expr\Array_::KIND_SHORT : Expr\Array_::KIND_LONG);
        $new_line = $node->getAttribute('new_line', false);
        if ($syntax === Expr\Array_::KIND_SHORT) {
            if ($new_line) {
                $t = $node->getAttribute('count_tab', 1);
                return "[\n" . str_repeat(" ", ($t*4)) . $this->pImplode($node->items, ",\n".str_repeat(" ", ($t*4))) . "\n".str_repeat(" ", ($t-1)*4)."]";
            } else {
                return '[' . $this->pCommaSeparated($node->items) . ']';
            }
        } else {
            if ($new_line) {
                return "array(\n\t" . $this->pImplode($node->items, ",\n\t") . "\n)";
            } else {
                return 'array(' . $this->pCommaSeparated($node->items) . ')';
            }
        }
    }
    /**
     * @inheritdoc
     */
    public function prettyPrintFile(array $stmts) {
        if (!$stmts) {
            return "<?php\n";
        }

        $p = "<?php\n" . $this->prettyPrint($stmts);

        if ($stmts[0] instanceof Node\Stmt\InlineHTML) {
            $p = preg_replace('/^<\?php\s+\?>\n?/', '', $p);
        }
        if ($stmts[count($stmts) - 1] instanceof Node\Stmt\InlineHTML) {
            $p = preg_replace('/<\?php$/', '', rtrim($p));
        }

        return $p;
    }
}