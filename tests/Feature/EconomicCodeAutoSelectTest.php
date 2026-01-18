<?php

namespace Tests\Feature;

use App\Livewire\Setup\EconomicCodes;
use App\Models\EconomicCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class EconomicCodeAutoSelectTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    // Mock permissions
    Gate::define('view-economic-codes', fn() => true);
    Gate::define('create-economic-codes', fn() => true);
    Gate::define('edit-economic-codes', fn() => true);
    Gate::define('delete-economic-codes', fn() => true);
  }

  public function test_auto_selects_parent_for_4_digit_code()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create Root Code
    $root = EconomicCode::create(['code' => '11', 'name' => 'Root']);

    Livewire::test(EconomicCodes::class)
      ->set('code', '1101')
      ->assertSet('selectedParentId', $root->id)
      ->assertSet('selectedSubHeadId', '');
  }

  public function test_auto_selects_parent_and_subhead_for_6_digit_code()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create Root and Sub Code
    $root = EconomicCode::create(['code' => '11', 'name' => 'Root']);
    $sub = EconomicCode::create(['code' => '1101', 'name' => 'Sub', 'parent_id' => $root->id]);

    Livewire::test(EconomicCodes::class)
      ->set('code', '110101')
      ->assertSet('selectedParentId', $root->id)
      ->assertSet('selectedSubHeadId', $sub->id);
  }

  public function test_resets_for_2_digit_code()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(EconomicCodes::class)
      ->set('selectedParentId', 999)
      ->set('code', '11')
      ->assertSet('selectedParentId', '')
      ->assertSet('selectedSubHeadId', '');
  }
  public function test_validation_fails_if_parent_missing_for_4_digit_code()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(EconomicCodes::class)
      ->set('name', 'Test Code')
      ->set('code', '1101')
      ->call('store')
      ->assertHasErrors(['selectedParentId']);
  }

  public function test_validation_fails_if_subhead_missing_for_6_digit_code()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $root = EconomicCode::create(['code' => '11', 'name' => 'Root']);

    Livewire::test(EconomicCodes::class)
      ->set('name', 'Test Code')
      ->set('code', '110101')
      ->call('store')
      ->assertHasErrors(['selectedSubHeadId']);
  }
}
