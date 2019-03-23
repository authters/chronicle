<?php

namespace Authters\Chronicle\Projection;

use MabeEnum\Enum;

/**
 * @method static ProjectionStatus RUNNING
 * @method static ProjectionStatus STOPPING
 * @method static ProjectionStatus DELETING
 * @method static ProjectionStatus DELETING_INCL_EMITTED_EVENTS
 * @method static ProjectionStatus RESETTING
 * @method static ProjectionStatus IDLE
 */
final class ProjectionStatus extends Enum
{
    const RUNNING = 'running';
    const STOPPING = 'stopping';
    const DELETING = 'deleting';
    const DELETING_INCL_EMITTED_EVENTS = 'deleting incl emitted events';
    const RESETTING = 'resetting';
    const IDLE = 'idle';
}