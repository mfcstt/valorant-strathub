#!/usr/bin/env node
/**
 * Copia os assets de terceiros de node_modules para public/vendor.
 *
 * Os ícones Phosphor vinham de `unpkg.com` a cada carregamento de página. Servir
 * do próprio domínio remove uma dependência de terceiros em tempo de execução
 * (a página não quebra se o CDN cair), elimina a necessidade de Subresource
 * Integrity numa URL sem versão e mantém o projeto funcionando offline.
 *
 * ## Por que só woff2
 *
 * O pacote traz cinco formatos da mesma fonte, somando ~12 MB nas duas variantes
 * usadas. Todo navegador que este projeto suporta lê woff2 - os outros formatos
 * são fallback para navegadores que não existem mais. Copiar só o woff2 reduz o
 * diretório para ~300 KB, e as regras `@font-face` são reescritas para apontar
 * apenas para ele, evitando 404 no console.
 */

import { cp, mkdir, rm, access, readFile, writeFile } from 'node:fs/promises'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

const projectRoot = join(dirname(fileURLToPath(import.meta.url)), '..')
const phosphorSource = join(projectRoot, 'node_modules', '@phosphor-icons', 'web', 'src')
const phosphorTarget = join(projectRoot, 'public', 'vendor', 'phosphor')

// Só as variantes usadas nas views: `ph` (regular) e `ph-fill`.
const VARIANTS = ['regular', 'fill']

async function exists(path) {
  try {
    await access(path)
    return true
  } catch {
    return false
  }
}

/**
 * Mantém no `src:` apenas a entrada woff2.
 */
function keepOnlyWoff2(css) {
  return css.replace(/src:\s*[^;]+;/g, (declaration) => {
    const woff2 = declaration.match(/url\("([^"]+\.woff2)"\)(\s*format\("woff2"\))?/)

    if (!woff2) {
      return declaration
    }

    return `src: url("${woff2[1]}") format("woff2");`
  })
}

async function main() {
  if (!(await exists(phosphorSource))) {
    console.error('✗ @phosphor-icons/web não encontrado. Rode `npm install` primeiro.')
    process.exit(1)
  }

  await rm(phosphorTarget, { recursive: true, force: true })

  for (const variant of VARIANTS) {
    const targetDir = join(phosphorTarget, variant)
    await mkdir(targetDir, { recursive: true })

    const woff2 = (await readFile(join(phosphorSource, variant, 'style.css'), 'utf8'))
      .match(/url\("\.\/([^"]+\.woff2)"\)/)?.[1]

    if (!woff2) {
      console.error(`✗ nenhum woff2 referenciado em ${variant}/style.css`)
      process.exit(1)
    }

    await cp(join(phosphorSource, variant, woff2), join(targetDir, woff2))

    const css = await readFile(join(phosphorSource, variant, 'style.css'), 'utf8')
    await writeFile(join(targetDir, 'style.css'), keepOnlyWoff2(css))

    console.log(`✓ ícones Phosphor (${variant}) → public/vendor/phosphor/${variant}`)
  }
}

await main()
