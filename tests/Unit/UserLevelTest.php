<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserLevelTest extends TestCase
{
    public function test_user_level_calculation_by_exp_thresholds(): void
    {
        $user = new User();

        // Level 1: 0 - 999 EXP
        $user->exp = 0;
        $this->assertEquals(1, $user->level);

        $user->exp = 999;
        $this->assertEquals(1, $user->level);

        // Level 2: 1,000 - 1,999 EXP
        $user->exp = 1000;
        $this->assertEquals(2, $user->level);

        $user->exp = 1999;
        $this->assertEquals(2, $user->level);

        // Level 3: 2,000 - 3,499 EXP
        $user->exp = 2000;
        $this->assertEquals(3, $user->level);

        $user->exp = 3499;
        $this->assertEquals(3, $user->level);

        // Level 4: 3,500 - 5,499 EXP
        $user->exp = 3500;
        $this->assertEquals(4, $user->level);

        $user->exp = 5499;
        $this->assertEquals(4, $user->level);

        // Level 5: 5,500 - 7,999 EXP
        $user->exp = 5500;
        $this->assertEquals(5, $user->level);
    }
}
