<?php
namespace Tests\Unit\Rules;
use App\Rules\GenresHaveCategoriesRule;
use Mockery\MockInterface;
use Tests\TestCase;

class GenresHaveCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenresHaveCategoriesRule([1,1,2,2]);
        $reflectionClass = new \ReflectionClass(GenresHaveCategoriesRule::class);
        $reflectionClassProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionClassProperty->setAccessible(true);
        $categoriesId = $reflectionClassProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $categoriesId);
    }

    public function testGenresIdValue()
    {
        $rule = $this->createRuleMock([]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturnNull();
        $rule->passes('', [1,1,2,2]);
        $reflectionClass = new \ReflectionClass(GenresHaveCategoriesRule::class);
        $reflectionClassProperty = $reflectionClass->getProperty('genresId');
        $reflectionClassProperty->setAccessible(true);
        $genresId = $reflectionClassProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $genresId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));
        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect());
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHaveCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1,2]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect(['category_id' => 1]));
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1,2]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect([['category_id' => 1], ['category_id' => 2]]));
        $this->assertTrue($rule->passes('', [1]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenresHaveCategoriesRule::class, [$categoriesId])->makePartial()->shouldAllowMockingProtectedMethods();
    }
}
