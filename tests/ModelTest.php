<?php
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch\Tests;

use ArrayAccess;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Matchory\Elasticsearch\ElasticsearchServiceProvider;
use Matchory\Elasticsearch\Model;
use Orchestra\Testbench\TestCase;

class ModelTest extends TestCase
{
    public function testResolveRouteBinding(): void
    {
    }

    public function testMergeCasts(): void
    {
    }

    public function testHasCast(): void
    {
    }

    public function testEncryptUsing(): void
    {
    }

    public function test__get(): void
    {
    }

    public function testGetCasts(): void
    {
    }

    public function testFillJsonAttribute(): void
    {
    }

    public function testOffsetExists(): void
    {
    }

    public function testAttributesToArray(): void
    {
    }

    public function testFromFloat(): void
    {
    }

    public function test__construct(): void
    {
        $model = new Model();

        self::assertInstanceOf(Arrayable::class, $model);
        self::assertInstanceOf(ArrayAccess::class, $model);
        self::assertInstanceOf(Jsonable::class, $model);
        self::assertInstanceOf(JsonSerializable::class, $model);
        self::assertInstanceOf(UrlRoutable::class, $model);
        self::assertInstanceOf(QueueableEntity::class, $model);
    }

    public function testExistsIsFalseByDefault(): void
    {
        $model = new Model();
        self::assertFalse($model->exists());
    }

    public function testExistsCanBePassedOnConstruction(): void
    {
        $model = new Model([], true);
        self::assertTrue($model->exists());
    }

    public function testOriginalIsEquivalent(): void
    {
        $model = new Model([
            'foo' => 'bar',
        ]);

        self::assertFalse($model->originalIsEquivalent('foo'));

        $model->syncOriginal();

        self::assertTrue($model->originalIsEquivalent('foo'));

        $model->foo = 'baz';

        self::assertFalse($model->originalIsEquivalent('foo'));
    }

    public function testGetQueueableRelations(): void
    {
        self::assertEmpty((new Model())->getQueueableRelations());
    }

    public function testGetAttribute(): void
    {
        $model = new Model([
            'foo' => 'bar',
        ]);

        self::assertSame(
            $model->getAttribute('foo'),
            $model->foo
        );

        self::assertSame(
            $model->getAttribute('foo'),
            'bar'
        );
    }

    public function testGetAttributeReturnsNullForMissingAttributes(): void
    {
        $model = new Model();

        self::assertNull($model->getAttribute('foo'));
    }

    public function testGetHighlights(): void
    {
    }

    public function testGetQueueableId(): void
    {
    }

    public function testSetIndex(): void
    {
    }

    public function testOffsetUnset(): void
    {
    }

    public function testGetRawOriginal(): void
    {
    }

    public function testSetDateFormat(): void
    {
    }

    public function testSetRawAttributes(): void
    {
    }

    public function test__unset(): void
    {
    }

    public function testSetConnection(): void
    {
    }

    public function testExists(): void
    {
    }

    public function testFind(): void
    {
    }

    public function testGetMutatedAttributes(): void
    {
    }

    public function testHasSetMutator(): void
    {
    }

    public function testSyncOriginalAttributes(): void
    {
    }

    public function testOnly(): void
    {
    }

    public function testFromEncryptedString(): void
    {
    }

    public function testGetAttributes(): void
    {
    }

    public function testGetUnSelectable(): void
    {
    }

    public function testHasGetMutator(): void
    {
    }

    public function testOffsetSet(): void
    {
    }

    public function testGetDates(): void
    {
    }

    public function testGetChanges(): void
    {
    }

    public function testCacheMutatedAttributes(): void
    {
    }

    public function testGetID(): void
    {
    }

    public function testGetSelectable(): void
    {
    }

    public function testIsDirty(): void
    {
    }

    public function testGetRouteKeyName(): void
    {
    }

    public function testGetKey(): void
    {
    }

    public function testSyncOriginalAttribute(): void
    {
    }

    public function testJsonSerialize(): void
    {
    }

    public function testGetIndex(): void
    {
    }

    public function testGetRelationValue(): void
    {
    }

    public function testOffsetGet(): void
    {
    }

    public function testToJson(): void
    {
    }

    public function test__call(): void
    {
    }

    public function testAppend(): void
    {
    }

    public function testGetDateFormat(): void
    {
    }

    public function test__isset(): void
    {
    }

    public function testDelete(): void
    {
    }

    public function testGetRouteKey(): void
    {
    }

    public function testSetType(): void
    {
    }

    public function testGetAttributeValue(): void
    {
    }

    public function testFromDateTime(): void
    {
    }

    public function testAll(): void
    {
    }

    public function testSave(): void
    {
    }

    public function testGetConnection(): void
    {
    }

    public function testWasChanged(): void
    {
    }

    public function testGetQueueableConnection(): void
    {
    }

    public function testSetAppends(): void
    {
    }

    public function testGetOriginal(): void
    {
    }

    public function testToArray(): void
    {
    }

    public function testGetType(): void
    {
    }

    public function testGetDirty(): void
    {
    }

    public function testSyncOriginal(): void
    {
    }

    public function testSetAttribute(): void
    {
    }

    public function testFromJson(): void
    {
    }

    public function testResolveChildRouteBinding(): void
    {
    }

    public function testRelationsToArray(): void
    {
    }

    public function testHasAppended(): void
    {
    }

    public function testIsClean(): void
    {
    }

    public function testSyncChanges(): void
    {
    }

    public function test__set(): void
    {
    }

    protected function getPackageProviders($app): array
    {
        return [
            ElasticsearchServiceProvider::class,
        ];
    }
}
