<?php

declare(strict_types=1);

use App\Actions\UserMemory\DeleteMemoryAction;
use App\Actions\UserMemory\ListMemoriesAction;
use App\Actions\UserMemory\ListMemoryTypesAction;
use App\Actions\UserMemory\SaveMemoryAction;
use App\AI\Tools\UserMemoryTool;
use App\Models\User;
use App\Models\UserMemory;

uses(\Illuminate\Foundation\Testing\LazilyRefreshDatabase::class);

describe('ListMemoryTypesAction', function () {
    test('returns message when no types exist', function () {
        $user = User::factory()->create();
        $action = new ListMemoryTypesAction;

        $result = $action->handle($user);

        expect($result)->toContain('No memory types found');
    });

    test('returns distinct types for user', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create(['user_id' => $user->id, 'type' => 'personal', 'key' => 'name']);
        UserMemory::factory()->create(['user_id' => $user->id, 'type' => 'personal', 'key' => 'birthday']);
        UserMemory::factory()->create(['user_id' => $user->id, 'type' => 'preferences', 'key' => 'color']);

        $action = new ListMemoryTypesAction;
        $result = $action->handle($user);

        expect($result)->toContain('personal');
        expect($result)->toContain('preferences');
    });

    test('does not return other users types', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        UserMemory::factory()->create(['user_id' => $userA->id, 'type' => 'personal']);
        UserMemory::factory()->create(['user_id' => $userB->id, 'type' => 'work']);

        $action = new ListMemoryTypesAction;
        $result = $action->handle($userA);

        expect($result)->toContain('personal');
        expect($result)->not->toContain('work');
    });
})->group('user-memory');

describe('ListMemoriesAction', function () {
    test('returns message when no memories exist for type', function () {
        $user = User::factory()->create();
        $action = new ListMemoriesAction;

        $result = $action->handle($user, 'personal');

        expect($result)->toContain("No memories found for type 'personal'");
    });

    test('returns memories for specified type', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'John Doe',
        ]);
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'birthday',
            'value' => 'January 1',
        ]);
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'work',
            'key' => 'company',
            'value' => 'Acme Inc',
        ]);

        $action = new ListMemoriesAction;
        $result = $action->handle($user, 'personal');

        expect($result)->toContain('name: John Doe');
        expect($result)->toContain('birthday: January 1');
        expect($result)->not->toContain('company');
    });

    test('does not return other users memories', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $userA->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'User A',
        ]);
        UserMemory::factory()->create([
            'user_id' => $userB->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'User B',
        ]);

        $action = new ListMemoriesAction;
        $result = $action->handle($userA, 'personal');

        expect($result)->toContain('User A');
        expect($result)->not->toContain('User B');
    });
})->group('user-memory');

describe('SaveMemoryAction', function () {
    test('creates new memory', function () {
        $user = User::factory()->create();
        $action = new SaveMemoryAction;

        $result = $action->handle($user, 'personal', 'name', 'John Doe');

        expect($result)->toContain('Saved');
        expect($result)->toContain('name: John Doe');

        $this->assertDatabaseHas('user_memories', [
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'John Doe',
        ]);
    });

    test('updates existing memory with same type and key', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'residence',
            'value' => 'Dallas, TX',
        ]);

        $action = new SaveMemoryAction;
        $result = $action->handle($user, 'personal', 'residence', 'Austin, TX');

        expect($result)->toContain('Updated');
        expect($result)->toContain('Austin, TX');

        $this->assertDatabaseCount('user_memories', 1);
        $this->assertDatabaseHas('user_memories', [
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'residence',
            'value' => 'Austin, TX',
        ]);
    });

    test('creates separate memories for different users with same type and key', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $action = new SaveMemoryAction;

        $action->handle($userA, 'personal', 'name', 'User A');
        $action->handle($userB, 'personal', 'name', 'User B');

        $this->assertDatabaseCount('user_memories', 2);
        $this->assertDatabaseHas('user_memories', ['user_id' => $userA->id, 'value' => 'User A']);
        $this->assertDatabaseHas('user_memories', ['user_id' => $userB->id, 'value' => 'User B']);
    });
})->group('user-memory');

describe('DeleteMemoryAction', function () {
    test('deletes existing memory', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'John Doe',
        ]);

        $action = new DeleteMemoryAction;
        $result = $action->handle($user, 'personal', 'name');

        expect($result)->toContain('Deleted');

        $this->assertDatabaseMissing('user_memories', [
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
        ]);
    });

    test('returns error message when memory does not exist', function () {
        $user = User::factory()->create();
        $action = new DeleteMemoryAction;

        $result = $action->handle($user, 'personal', 'nonexistent');

        expect($result)->toContain('No memory found');
    });

    test('does not delete other users memories', function () {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $userA->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'User A',
        ]);

        $action = new DeleteMemoryAction;
        $result = $action->handle($userB, 'personal', 'name');

        expect($result)->toContain('No memory found');

        $this->assertDatabaseHas('user_memories', [
            'user_id' => $userA->id,
            'value' => 'User A',
        ]);
    });
})->group('user-memory');

describe('UserMemoryTool', function () {
    test('list_types action returns types', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create(['user_id' => $user->id, 'type' => 'personal']);

        $tool = new UserMemoryTool($user);
        $result = $tool('list_types');

        expect($result)->toContain('personal');
    });

    test('list_memories action returns memories', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'John',
        ]);

        $tool = new UserMemoryTool($user);
        $result = $tool('list_memories', 'personal');

        expect($result)->toContain('name: John');
    });

    test('list_memories action requires type parameter', function () {
        $user = User::factory()->create();

        $tool = new UserMemoryTool($user);
        $result = $tool('list_memories');

        expect($result)->toContain('Error: type parameter is required');
    });

    test('save action creates memory', function () {
        $user = User::factory()->create();

        $tool = new UserMemoryTool($user);
        $result = $tool('save', 'personal', 'name', 'John');

        expect($result)->toContain('Saved');

        $this->assertDatabaseHas('user_memories', [
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
            'value' => 'John',
        ]);
    });

    test('save action requires all parameters', function () {
        $user = User::factory()->create();

        $tool = new UserMemoryTool($user);
        $result = $tool('save', 'personal', 'name');

        expect($result)->toContain('Error');
        expect($result)->toContain('required');
    });

    test('delete action removes memory', function () {
        $user = User::factory()->create();
        UserMemory::factory()->create([
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
        ]);

        $tool = new UserMemoryTool($user);
        $result = $tool('delete', 'personal', 'name');

        expect($result)->toContain('Deleted');

        $this->assertDatabaseMissing('user_memories', [
            'user_id' => $user->id,
            'type' => 'personal',
            'key' => 'name',
        ]);
    });

    test('invalid action returns error', function () {
        $user = User::factory()->create();

        $tool = new UserMemoryTool($user);
        $result = $tool('invalid_action');

        expect($result)->toContain('Unknown action');
    });
})->group('user-memory');
