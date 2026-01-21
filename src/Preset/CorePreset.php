<?php

declare(strict_types=1);

namespace PhpMjml\Preset;

use PhpMjml\Components\Body\Body;
use PhpMjml\Components\Body\Section;
use PhpMjml\Components\Body\Column;
use PhpMjml\Components\Body\Text;

final class CorePreset
{
    /**
     * @return array<class-string>
     */
    public static function getComponents(): array
    {
        return [
            // Body components
            Body::class,
            Section::class,
            Column::class,
            Text::class,
        ];
    }
}
