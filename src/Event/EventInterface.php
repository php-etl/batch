<?php

namespace Kiboko\Component\Workflow\Event;

/**
 * Interface of the batch component events
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface EventInterface
{
    /** Step execution events */
    const BEFORE_STEP_EXECUTION      = 'akeneo_batch.before_step_execution';
    const STEP_EXECUTION_SUCCEEDED   = 'akeneo_batch.step_execution_succeeded';
    const STEP_EXECUTION_INTERRUPTED = 'akeneo_batch.step_execution_interrupted';
    const STEP_EXECUTION_ERRORED     = 'akeneo_batch.step_execution_errored';
    const STEP_EXECUTION_COMPLETED   = 'akeneo_batch.step_execution_completed';
    const INVALID_ITEM               = 'akeneo_batch.invalid_item';
}
