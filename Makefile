
# Ubuntu settings
WWW_OWNER=www-data
WWW_GROUP=www-data

DESTDIR=/usr/share/geni-ch
WWWDIR=$(DESTDIR)/www
LIBDIR=$(DESTDIR)/php
DBDIR=$(DESTDIR)/db
PGDIR=$(DBDIR)/postgresql

# Programs
RSYNC = /usr/bin/rsync
INSTALL ?= /usr/bin/install
WWWINSTALL = $(INSTALL) -o $(WWW_OWNER) -g $(WWW_GROUP)

.PHONY: default install clean distclean
.PHONY: syncm syncd synci syncs syncp

default:
	@echo "Try make install"

install:
	$(WWWINSTALL) -d $(DESTDIR)
	for d in lib portal sa sr; do \
	  (cd "$${d}" && $(MAKE) $@) \
	done

syncd:
	$(RSYNC) --exclude .git -aztv ../proto-ch dagoola.gpolab.bbn.com:

syncm:
	$(RSYNC) --exclude .git -aztv ../proto-ch marilac.gpolab.bbn.com:

synci:
	$(RSYNC) --exclude .git -aztv ../proto-ch illyrica.gpolab.bbn.com:

syncs:
	$(RSYNC) --exclude .git -aztv ../proto-ch sergyar.gpolab.bbn.com:

syncp:
	$(RSYNC) --exclude .git -aztv ../proto-ch panther.gpolab.bbn.com:

clean:

distclean:
	find . -name '*~' -exec rm {} \;
