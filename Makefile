
CHECK	=	$(shell find src/ -name "*.php" -or -name "*.phtml")

all:
install:
		./install.sh

$(CHECK):
		@php -l $@ > /dev/null
check:		$(CHECK)
#		@(cd tests/ && ./run.sh)
		@(./tests/training_attendance.sh)

.PHONY:		$(CHECK) check all install

