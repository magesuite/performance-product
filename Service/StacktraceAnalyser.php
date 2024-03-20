<?php

namespace MageSuite\PerformanceProduct\Service;

// phpcs:disable Standard.TooMany.IfConditions.Found
class StacktraceAnalyser
{
    public function isInvokedBy(?string $className = null, ?string $methodName = null, $maximumCheckedDepth = 10)
    {
        $checkedDepth = 0;

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT) as $trace) {
            if ($maximumCheckedDepth > 0 && $checkedDepth >= $maximumCheckedDepth) {
                return false;
            }

            if (!empty($className) &&
                !empty($methodName) &&
                isset($trace['object']) &&
                is_a($trace['object'], $className) &&
                $trace['function'] === $methodName
            ) {
                return true;
            } elseif (!empty($className) &&
                empty($methodName) &&
                isset($trace['object']) &&
                is_a($trace['object'], $className)
            ) {
                return true;
            } elseif (empty($className) && !empty($methodName) && $trace['function'] === $methodName) {
                return true;
            }

            $checkedDepth++;
        }

        return false;
    }
}
